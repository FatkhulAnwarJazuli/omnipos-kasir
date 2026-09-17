<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
  public function index(Request $request)
  {
    $brand = $request->query('brand', 'all');
    $products = Product::with('category')->get();
    $categories = Category::all();
    $regularTiers = PriceTier::where('group_type', 'regular')->orderBy('min_gram')->get();
    $premiumTiers = PriceTier::where('group_type', 'premium')->orderBy('min_gram')->get();

    return view('pos.index', compact('products', 'categories', 'regularTiers', 'premiumTiers', 'brand'));
  }

    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'payment_method' => 'required|in:cash,qris',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            foreach ($request->cart as $item) {
                $totalAmount += $item['subtotal'];
            }

            // 1. Buat Header Transaksi
            $transaction = Transaction::create([
                'transaction_code' => 'TRX-' . date('YmdHis') . '-' . rand(100, 999),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
            ]);

            // 2. Simpan Detail & Potong Stok
            foreach ($request->cart as $item) {
                $product = Product::findOrFail($item['id']);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty_pcs' => $product->unit_type === 'pcs' ? $item['qty'] : null,
                    'weight_actual_gram' => $product->unit_type !== 'pcs' ? $item['weight_actual'] : null,
                    'tier_applied' => $item['tier_applied'] ?? null,
                    'subtotal' => $item['subtotal'],
                ]);

                // Potong Stok Gudang (Gram/Pcs)
                $deduction = $product->unit_type === 'pcs' ? $item['qty'] : $item['weight_actual'];
                $product->decrement('stock', $deduction);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil!',
                'transaction_code' => $transaction->transaction_code,
            ]);
        });
    }
}
