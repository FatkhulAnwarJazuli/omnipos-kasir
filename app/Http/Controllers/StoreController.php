<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::withCount('products')->latest()->get();
        return view('stores.index', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $store = new Store();
        $store->name = $request->name;
        
        // Otomatis isi slug dan code jika kolomnya ada di database
        if (Schema::hasColumn('stores', 'slug')) {
            $store->slug = Str::slug($request->name) . '-' . rand(100, 999);
        }

        if (Schema::hasColumn('stores', 'code')) {
            $store->code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $request->name), 0, 3)) . rand(10, 99);
        }

        $store->save();

        return redirect()->route('stores.index')->with('success', 'Toko baru berhasil ditambahkan!');
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $store->name = $request->name;

        if (Schema::hasColumn('stores', 'slug')) {
            $store->slug = Str::slug($request->name);
        }

        $store->save();

        return redirect()->route('stores.index')->with('success', 'Data toko berhasil diperbarui!');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Toko berhasil dihapus!');
    }
}