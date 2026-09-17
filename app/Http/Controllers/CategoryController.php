<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori beserta data toko untuk modal pop-up.
     */
    public function index()
    {
        $stores = Store::all();
        $categories = Category::with('store')->withCount('products')->latest()->get();
        return view('categories.index', compact('categories', 'stores'));
    }

    /**
     * Menyimpan kategori baru dari modal create.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name'     => 'required|string|max:255',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    /**
     * Memperbarui data kategori dari modal edit.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name'     => 'required|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}