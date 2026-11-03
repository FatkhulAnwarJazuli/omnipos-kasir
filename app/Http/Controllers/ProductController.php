<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\Store;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->get('search');
        $storeId     = $request->get('store_id');
        $categoryId  = $request->get('category_id');
        $unitType    = $request->get('unit_type');
        $stockStatus = $request->get('stock_status');
        $sortBy      = $request->get('sort_by', 'latest');

        $query = Product::with(['store', 'category', 'supplier', 'batches' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }])->withSum(['batches as total_stock' => function ($q) {
            $q->where('current_stock', '>', 0);
        }], 'current_stock');

        // Filter Nama Produk
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter Toko
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Filter Kategori
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter Satuan
        if ($unitType) {
            $query->where('unit_type', $unitType);
        }

        // Sorting
        if ($sortBy === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sortBy === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sortBy === 'stock_asc') {
            $query->orderBy('total_stock', 'asc');
        } elseif ($sortBy === 'stock_desc') {
            $query->orderBy('total_stock', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->get();

        // Filter Status Stok
        if ($stockStatus === 'empty') {
            $products = $products->filter(fn($p) => ($p->total_stock ?? 0) <= 0);
        } elseif ($stockStatus === 'low') {
            $products = $products->filter(fn($p) => ($p->total_stock ?? 0) > 0 && ($p->total_stock ?? 0) <= 5);
        } elseif ($stockStatus === 'available') {
            $products = $products->filter(fn($p) => ($p->total_stock ?? 0) > 5);
        }

        $stores = Store::all();
        $categories = Category::all();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.index', compact(
            'products', 'stores', 'categories', 'suppliers',
            'search', 'storeId', 'categoryId', 
            'unitType', 'stockStatus', 'sortBy'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'store_id'        => 'required|exists:stores,id',
            'category_id'     => 'required|exists:categories,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'unit_type'       => 'required|in:pcs,gram,ml',
            'sell_price'      => 'required_if:unit_type,pcs|nullable|numeric|min:0',
            'image'           => 'nullable|file|mimes:jpg,jpeg,png,webp,bmp,gif,heic,heif|max:5120',
            'total_buy_price' => 'required|numeric|min:0',
            'initial_stock'   => 'required|numeric|min:1',
            'price_templates'          => 'required_if:unit_type,gram,ml|nullable|array|min:1',
            'price_templates.*.amount' => 'required|numeric|min:1',
            'price_templates.*.price'  => 'required|numeric|min:0',
        ]);

        $isPcs = $validated['unit_type'] === 'pcs';

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Hitung Modal HPP per Satuan Otomatis
        $buyPricePerUnit = $validated['total_buy_price'] / $validated['initial_stock'];

        $product = Product::create([
            'name'            => $validated['name'],
            'store_id'        => $validated['store_id'],
            'category_id'     => $validated['category_id'],
            'supplier_id'     => $validated['supplier_id'] ?? null,
            'unit_type'       => $validated['unit_type'],
            'sell_price'      => $isPcs ? $validated['sell_price'] : 0,
            'image'           => $imagePath,
            'price_templates' => $isPcs ? [] : $this->normalizeTemplates($validated['price_templates'] ?? []),
        ]);

        ProductBatch::create([
            'product_id'    => $product->id,
            'batch_code'    => 'BATCH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product->name), 0, 3)) . '-' . date('YmdHis'),
            'buy_price'     => $buyPricePerUnit,
            'sell_price'    => 0,
            'initial_stock' => $validated['initial_stock'],
            'current_stock' => $validated['initial_stock'],
            'purchased_at'  => now(),
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    // RESTOK SUPER SIMPEL (Tanpa Harga Jual)
    public function addBatch(Request $request, Product $product)
    {
        $validated = $request->validate([
            'initial_stock'   => 'required|numeric|min:1',
            'total_buy_price' => 'required|numeric|min:0',
        ]);

        $buyPricePerUnit = $validated['total_buy_price'] / $validated['initial_stock'];

        ProductBatch::create([
            'product_id'    => $product->id,
            'batch_code'    => 'BATCH-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $product->name), 0, 3)) . '-' . date('YmdHis'),
            'buy_price'     => $buyPricePerUnit,
            'sell_price'    => 0,
            'initial_stock' => $validated['initial_stock'],
            'current_stock' => $validated['initial_stock'],
            'purchased_at'  => now(),
        ]);

        return redirect()->route('products.index')->with('success', 'Stok berhasil ditambahkan!');
    }

    // UPDATE TEMPLATE HARGA JUAL
    public function updateTemplates(Request $request, Product $product)
    {
        $validated = $request->validate([
            'price_templates'          => 'nullable|array',
            'price_templates.*.amount' => 'required|numeric|min:1',
            'price_templates.*.price'  => 'required|numeric|min:0',
        ]);

        $product->update([
            'price_templates' => $this->normalizeTemplates($validated['price_templates'] ?? []),
        ]);

        return redirect()->route('products.index')->with('success', 'Template harga berhasil diperbarui!');
    }

    // FITUR STOCK OPNAME (PENYESUAIAN STOK)
    public function stockOpname(Request $request, Product $product)
    {
        $validated = $request->validate([
            'physical_stock' => 'required|numeric|min:0',
            'reason'         => 'required|string',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $systemStock = $product->batches->sum('current_stock');
            $diff = $validated['physical_stock'] - $systemStock;

            // Catat Log Opname
            StockOpname::create([
                'product_id'     => $product->id,
                'system_stock'   => $systemStock,
                'physical_stock' => $validated['physical_stock'],
                'difference'     => $diff,
                'reason'         => $validated['reason'],
                'notes'          => $validated['notes'] ?? null,
            ]);

            // Penyesuaian ke Batch FIFO
            if ($diff < 0) {
                $deductNeeded = abs($diff);
                $batches = ProductBatch::where('product_id', $product->id)
                    ->where('current_stock', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($deductNeeded <= 0) break;
                    $take = min($batch->current_stock, $deductNeeded);
                    $batch->current_stock -= $take;
                    $batch->save();
                    $deductNeeded -= $take;
                }
            } elseif ($diff > 0) {
                $lastBatch = ProductBatch::where('product_id', $product->id)->latest()->first();
                if ($lastBatch) {
                    $lastBatch->current_stock += $diff;
                    $lastBatch->save();
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Stock opname berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Gagal simpan opname: ' . $e->getMessage());
        }
    }

    private function normalizeTemplates(array $templates): array
    {
        return collect($templates)
            ->map(fn ($t) => [
                'amount' => (float) $t['amount'] == (int) $t['amount'] ? (int) $t['amount'] : (float) $t['amount'],
                'price'  => (float) $t['price'] == (int) $t['price'] ? (int) $t['price'] : (float) $t['price'],
            ])
            ->sortBy('amount')
            ->values()
            ->all();
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'store_id'    => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_type'   => 'required|in:pcs,gram,ml',
            'sell_price'  => 'required_if:unit_type,pcs|nullable|numeric|min:0',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,webp,bmp,gif,heic,heif|max:5120',
        ]);

        $validated['sell_price'] = $validated['unit_type'] === 'pcs' ? $validated['sell_price'] : 0;
        $validated['supplier_id'] = $validated['supplier_id'] ?? null;

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }
}