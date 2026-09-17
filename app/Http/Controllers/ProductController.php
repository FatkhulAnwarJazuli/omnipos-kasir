<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk beserta data relasi untuk modal create/edit.
     */
    public function index()
    {
        $products   = Product::with(['store', 'category', 'supplier', 'batches'])->latest()->get();
        $stores     = Store::all();
        $categories = Category::all();
        $suppliers  = Supplier::all();

        return view('products.index', compact('products', 'stores', 'categories', 'suppliers'));
    }

    /**
     * Simpan produk baru + batch perdana dari modal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id'       => 'required|exists:stores,id',
            'category_id'    => 'required|exists:categories,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'name'           => 'required|string|max:255',
            'unit_type'      => 'required|in:pcs,gram,bucket',
            'buy_price'      => 'required|numeric|min:0',
            'sell_price'     => 'required|numeric|min:0',
            'initial_stock'  => 'required|numeric|min:1',
        ]);

        $product = Product::create([
            'store_id'       => $validated['store_id'],
            'category_id'    => $validated['category_id'],
            'supplier_id'    => $validated['supplier_id'] ?? null,
            'name'           => $validated['name'],
            'unit_type'      => $validated['unit_type'],
            'is_consignment' => $request->has('is_consignment'),
        ]);

        $product->batches()->create([
            'batch_code'    => 'BATCH-' . strtoupper(substr(uniqid(), -5)),
            'buy_price'     => $validated['buy_price'],
            'sell_price'    => $validated['sell_price'],
            'initial_stock' => $validated['initial_stock'],
            'current_stock' => $validated['initial_stock'],
        ]);

        return redirect()->route('products.index')->with('success', 'Produk baru & Stok Batch awal berhasil disimpan!');
    }

    /**
     * Update data induk produk dari modal edit.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name'        => 'required|string|max:255',
            'unit_type'   => 'required|in:pcs,gram,bucket',
        ]);

        $validated['is_consignment'] = $request->has('is_consignment');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data produk berhasil diperbarui!');
    }

    /**
     * Hapus produk beserta batch-nya.
     */
    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->batches()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Tambah batch stok pasokan baru dari modal batch.
     */
    public function storeBatch(Request $request, Product $product)
    {
        $validated = $request->validate([
            'batch_code'    => 'required|string|max:255',
            'buy_price'     => 'required|numeric|min:0',
            'sell_price'    => 'required|numeric|min:0',
            'initial_stock' => 'required|numeric|min:1',
        ]);

        $product->batches()->create([
            'batch_code'    => $validated['batch_code'],
            'buy_price'     => $validated['buy_price'],
            'sell_price'    => $validated['sell_price'],
            'initial_stock' => $validated['initial_stock'],
            'current_stock' => $validated['initial_stock'],
        ]);

        return redirect()->route('products.index')->with('success', 'Batch stok baru berhasil ditambahkan!');
    }
}