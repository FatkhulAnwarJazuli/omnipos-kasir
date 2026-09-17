<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::withCount('products')->latest()->get();
        return view('stores.index', compact('stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('stores', 'public');
        }

        Store::create($validated);

        return redirect()->route('stores.index')->with('success', 'Toko berhasil ditambahkan!');
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores', 'public');
        }

        $store->update($validated);

        return redirect()->route('stores.index')->with('success', 'Data toko berhasil diperbarui!');
    }

    public function destroy(Store $store)
    {
        if ($store->logo && Storage::disk('public')->exists($store->logo)) {
            Storage::disk('public')->delete($store->logo);
        }

        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Toko berhasil dihapus!');
    }
}