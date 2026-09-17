<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'supplier'])->latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();
        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:255',
            'unit_type'      => 'required|in:gram,pcs,bucket',
            'buy_price'      => 'nullable|numeric|min:0',
            'sell_price'     => 'nullable|numeric|min:0',
            'stock'          => 'required|numeric|min:0',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supplier_id'    => 'nullable|exists:suppliers,id',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'category_id'    => $request->category_id,
            'supplier_id'    => $request->supplier_id,
            'is_consignment' => $request->has('is_consignment'),
            'name'           => $request->name,
            'image'          => $imagePath,
            'unit_type'      => $request->unit_type,
            'buy_price'      => $request->buy_price ?? 0,
            'sell_price'     => $request->sell_price ?? 0,
            'stock'          => $request->stock,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
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