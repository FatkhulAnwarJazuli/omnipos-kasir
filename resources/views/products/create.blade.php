<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Tambah Produk Baru</h2>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800">← Kembali</a>
        </div>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            @csrf

            <!-- Nama Produk -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="name" required placeholder="Misal: Keripik Balado / Cincin Titanium" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
            </div>

            <!-- Kategori & Tipe Unit -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Penjualan (Unit)</label>
                    <select name="unit_type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                        <option value="pcs">Pcs (Aksesoris / Snack Kemasan)</option>
                        <option value="gram">Gram (Snack Prasmanan Timbangan)</option>
                        <option value="bucket">Bucket (Prasmanan Paket Bucket)</option>
                    </select>
                </div>
            </div>

            <!-- Harga Modal, Harga Jual & Stok -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Harga Modal / Setoran (HPP)</label>
                    <input type="number" name="buy_price" value="0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Harga Jual Kasir</label>
                    <input type="number" name="sell_price" value="0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Stok Initial</label>
                    <input type="number" name="stock" value="100" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                </div>
            </div>

            <!-- Upload Foto Produk -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Foto Produk (Opsional)</label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>

            <!-- Checkbox Konsinyasi UMKM -->
            <div x-data="{ isConsignment: false }" class="pt-2 border-t border-gray-100 space-y-3">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_consignment" x-model="isConsignment" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                    <span class="text-xs font-bold text-gray-800">Ini Adalah Produk Titipan / Konsinyasi UMKM</span>
                </label>

                <div x-show="isConsignment" x-cloak>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pilih UMKM / Supplier Penitip</label>
                    <select name="supplier_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-amber-500">
                        <option value="">-- Pilih Supplier UMKM --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md transition">
                Simpan Produk Baru
            </button>
        </form>
    </div>
</x-app-layout>