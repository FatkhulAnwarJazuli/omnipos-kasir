<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with(['store', 'product'])->latest()->get();
        $stores = Store::orderBy('name')->get();
        $products = Product::with('store')->orderBy('name')->get();

        return view('discounts.index', compact('discounts', 'stores', 'products'));
    }

    public function store(Request $request)
    {
        Discount::create($this->validated($request));

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dibuat!');
    }

    public function update(Request $request, Discount $discount)
    {
        $discount->update($this->validated($request));

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil diperbarui!');
    }

    // Hidupkan / matikan diskon tanpa perlu menghapusnya
    public function toggle(Discount $discount)
    {
        $discount->update(['is_active' => !$discount->is_active]);

        return redirect()->route('discounts.index')->with(
            'success',
            $discount->is_active ? 'Diskon diaktifkan.' : 'Diskon dimatikan.'
        );
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dihapus!');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percent,nominal',
            'value'      => ['required', 'numeric', 'min:0.01', Rule::when($request->input('type') === 'percent', ['max:100'])],
            'scope'      => 'required|in:all,store,product',
            'store_id'   => 'required_if:scope,store|nullable|exists:stores,id',
            'product_id' => 'required_if:scope,product|nullable|exists:products,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            'value.max'                => 'Diskon persen maksimal 100%.',
            'store_id.required_if'     => 'Pilih toko untuk diskon ini.',
            'product_id.required_if'   => 'Pilih produk untuk diskon ini.',
            'end_date.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Simpan hanya target yang sesuai cakupan
        if ($data['scope'] !== 'store') {
            $data['store_id'] = null;
        }
        if ($data['scope'] !== 'product') {
            $data['product_id'] = null;
        }

        return $data;
    }
}
