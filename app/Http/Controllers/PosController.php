<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $stores = Store::all();
        $categories = Category::all();

        // Hanya kirim total stok ke browser (modal/HPP batch tidak ikut terkirim)
        $products = Product::with('store')
            ->withSum(['batches as total_stock' => fn ($q) => $q->where('current_stock', '>', 0)], 'current_stock')
            ->orderBy('name')
            ->get();

        $discounts = Discount::active()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'value', 'scope', 'store_id', 'product_id']);

        return view('pos.index', compact('stores', 'categories', 'products', 'discounts'));
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart'                   => 'required|array|min:1|max:100',
            'cart.*.product_id'      => 'required|integer|exists:products,id',
            'cart.*.template_amount' => 'nullable|numeric|min:0',
            'cart.*.qty'             => 'required|integer|min:1|max:999',
            'discount_id'            => 'nullable|integer',
            'pay_amount'             => 'nullable|numeric|min:0',
            'payment_method'         => 'required|in:cash,qris,transfer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            // Jika ada error apa pun di dalam, seluruh perubahan otomatis dibatalkan (rollback)
            $invoice = DB::transaction(fn () => $this->processCheckout($validator->validated()));

            return response()->json(['success' => true, 'invoice' => $invoice]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Checkout gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'error' => 'Terjadi kesalahan sistem. Transaksi tidak tersimpan.'], 500);
        }
    }

    private function processCheckout(array $data): array
    {
        $products = Product::with('store')
            ->whereIn('id', collect($data['cart'])->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        // 1. Harga SELALU dihitung dari data produk di server (angka dari browser diabaikan)
        $lines = [];
        foreach ($data['cart'] as $row) {
            $product = $products[$row['product_id']];
            $qty = (int) $row['qty'];

            if ($product->unit_type === 'pcs') {
                $unitPrice = (float) $product->sell_price;
                if ($unitPrice <= 0) {
                    throw new \DomainException("Harga jual {$product->name} belum diatur.");
                }
                $unitsPerQty = 1.0;
                $portionLabel = 'pcs';
            } else {
                $requested = (float) ($row['template_amount'] ?? 0);
                $template = collect($product->price_templates ?? [])
                    ->first(fn ($t) => abs((float) $t['amount'] - $requested) < 0.0001);

                if (!$template) {
                    throw new \DomainException("Porsi {$product->name} tidak ada di daftar harga.");
                }

                $unitPrice = (float) $template['price'];
                $unitsPerQty = (float) $template['amount'];
                $portionLabel = $this->formatAmount($unitsPerQty) . ' ' . ($product->unit_type === 'gram' ? 'gr' : $product->unit_type);
            }

            $lines[] = [
                'product'       => $product,
                'qty'           => $qty,
                'unit_price'    => $unitPrice,
                'units'         => $unitsPerQty * $qty,
                'line_total'    => round($unitPrice * $qty, 2),
                'portion_label' => $portionLabel,
                'discount'      => 0.0,
            ];
        }

        // 2. Kunci batch & pastikan stok cukup
        $needed = [];
        foreach ($lines as $line) {
            $pid = $line['product']->id;
            $needed[$pid] = ($needed[$pid] ?? 0) + $line['units'];
        }

        $batchesByProduct = [];
        foreach ($needed as $pid => $units) {
            $batches = ProductBatch::where('product_id', $pid)
                ->where('current_stock', '>', 0)
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $available = (float) $batches->sum('current_stock');
            if ($available + 0.0001 < $units) {
                $p = $products[$pid];
                throw new \DomainException("Stok {$p->name} tidak cukup (tersisa " . $this->formatAmount($available) . " {$p->unit_type}).");
            }

            $batchesByProduct[$pid] = $batches;
        }

        // 3. Diskon (opsional)
        $subtotal = round(collect($lines)->sum('line_total'), 2);
        $discount = null;
        $discountAmount = 0.0;

        if (!empty($data['discount_id'])) {
            $discount = Discount::active()->find($data['discount_id']);
            if (!$discount) {
                throw new \DomainException('Diskon yang dipilih sudah tidak berlaku.');
            }

            $eligible = [];
            foreach ($lines as $i => $line) {
                if ($this->discountApplies($discount, $line['product'])) {
                    $eligible[] = $i;
                }
            }

            $eligibleTotal = round(collect($eligible)->sum(fn ($i) => $lines[$i]['line_total']), 2);

            if ($eligibleTotal > 0) {
                $discountAmount = $discount->type === 'percent'
                    ? round($eligibleTotal * (float) $discount->value / 100)
                    : round((float) $discount->value);
                $discountAmount = min($discountAmount, $eligibleTotal);

                // Potongan dibagi proporsional ke tiap barang yang kena diskon
                $shares = $this->allocate($discountAmount, array_map(fn ($i) => $lines[$i]['line_total'], $eligible));
                foreach ($eligible as $k => $i) {
                    $lines[$i]['discount'] = $shares[$k];
                }
            }

            if ($discountAmount <= 0) {
                $discount = null; // diskon tidak berlaku untuk isi keranjang ini
            }
        }

        $total = round($subtotal - $discountAmount, 2);

        // 4. Pembayaran
        $method = $data['payment_method'];
        $pay = $method === 'cash' ? (float) ($data['pay_amount'] ?? 0) : $total;
        if ($pay + 0.0001 < $total) {
            throw new \DomainException('Uang pembayaran kurang!');
        }

        // 5. Simpan transaksi
        $transaction = Transaction::create([
            'invoice_number'  => 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(3)),
            'subtotal_amount' => $subtotal,
            'total_amount'    => $total,
            'discount_id'     => $discount?->id,
            'discount_name'   => $discount?->name,
            'discount_amount' => $discountAmount,
            'pay_amount'      => $pay,
            'change_amount'   => max(0, round($pay - $total, 2)),
            'payment_method'  => $method,
            'status'          => 'completed',
        ]);

        // 6. Potong stok FIFO per batch dan catat detail
        $receiptLines = [];
        foreach ($lines as $line) {
            $product = $line['product'];
            $remaining = $line['units'];
            $chunks = [];

            foreach ($batchesByProduct[$product->id] as $batch) {
                if ($remaining <= 0.0001) {
                    break;
                }
                $available = (float) $batch->current_stock;
                if ($available <= 0) {
                    continue;
                }

                $take = min($available, $remaining);
                $batch->current_stock = round($available - $take, 2);
                $batch->save();
                $remaining = round($remaining - $take, 4);

                $chunks[] = ['batch' => $batch, 'take' => $take];
            }

            $takes = array_column($chunks, 'take');
            $moneyShares = $this->allocate($line['line_total'], $takes);
            $discountShares = $this->allocate($line['discount'], $takes);

            foreach ($chunks as $k => $chunk) {
                TransactionDetail::create([
                    'transaction_id'   => $transaction->id,
                    'store_id'         => $product->store_id,
                    'product_id'       => $product->id,
                    'product_batch_id' => $chunk['batch']->id,
                    'quantity'         => $chunk['take'],
                    'buy_price'        => $chunk['batch']->buy_price,
                    'sell_price'       => round($line['line_total'] / $line['units'], 2),
                    'subtotal'         => $moneyShares[$k],
                    'discount_amount'  => $discountShares[$k],
                ]);
            }

            $receiptLines[] = [
                'name'       => $product->name,
                'store'      => $product->store->name ?? '-',
                'qty'        => $line['qty'],
                'portion'    => $line['portion_label'],
                'unit_price' => $line['unit_price'],
                'line_total' => $line['line_total'],
                'discount'   => $line['discount'],
            ];
        }

        return [
            'invoice_number'  => $transaction->invoice_number,
            'date'            => $transaction->created_at->format('d/m/Y H:i'),
            'lines'           => $receiptLines,
            'subtotal_amount' => $subtotal,
            'discount_name'   => $discount?->name,
            'discount_amount' => $discountAmount,
            'total_amount'    => $total,
            'pay_amount'      => $pay,
            'change_amount'   => max(0, round($pay - $total, 2)),
            'payment_method'  => $method,
        ];
    }

    public function voidTransaction(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        // Cari transaksi berdasarkan ID murni atau Nomor Invoice
        $transaction = Transaction::where('id', $id)
            ->orWhere('invoice_number', $id)
            ->orWhere('invoice_number', 'like', '%' . $id . '%')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false, 
                'error'   => 'Transaksi tidak ditemukan. Periksa kembali ID atau Nomor Nota.'
            ], 444);
        }

        if ($transaction->status === 'cancelled') {
            return response()->json([
                'success' => false, 
                'error'   => 'Transaksi ini sudah dibatalkan sebelumnya.'
            ], 422);
        }

        try {
            DB::transaction(function () use ($transaction, $validated) {
                // 1. Rollback / Pengembalian stok FIFO ke batch masing-masing
                foreach ($transaction->details as $detail) {
                    if ($detail->batch) {
                        $detail->batch->increment('current_stock', $detail->quantity);
                    }
                }

                // 2. Ubah status transaksi menjadi cancelled
                $transaction->update([
                    'status'        => 'cancelled',
                    'cancel_reason' => $validated['reason'],
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$transaction->invoice_number} berhasil dibatalkan dan stok telah dikembalikan.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Void transaksi gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false, 
                'error'   => 'Terjadi kesalahan sistem saat membatalkan transaksi.'
            ], 500);
        }
    }

    private function discountApplies(Discount $discount, Product $product): bool
    {
        return match ($discount->scope) {
            'store'   => (int) $discount->store_id === (int) $product->store_id,
            'product' => (int) $discount->product_id === (int) $product->id,
            default   => true,
        };
    }

    private function allocate(float $amount, array $weights): array
    {
        $weights = array_values($weights);
        $count = count($weights);
        if ($count === 0) {
            return [];
        }

        $sum = array_sum($weights);
        $shares = [];
        $allocated = 0.0;

        foreach ($weights as $i => $w) {
            if ($i === $count - 1) {
                $shares[] = round($amount - $allocated, 2);
                break;
            }
            $share = $sum > 0 ? round($amount * $w / $sum, 2) : 0.0;
            $shares[] = $share;
            $allocated += $share;
        }

        return $shares;
    }

    private function formatAmount(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}