<x-app-layout>
    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Stok & Produk</h2>
                <p class="text-xs text-gray-500">Kelola katalog barang Markasnack & Glytch</p>
            </div>
            <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition">
                + Tambah Produk
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabel Produk -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4">Produk</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Tipe / Stok</th>
                        <th class="p-4">Harga Modal / Jual</th>
                        <th class="p-4">Konsinyasi / UMKM</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-200 flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-lg">{{ $product->unit_type === 'gram' ? '🍿' : '💍' }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-gray-800">{{ $product->name }}</p>
                                <p class="text-[10px] text-gray-400">ID: #{{ $product->id }}</p>
                            </div>
                        </td>
                        <td class="p-4 font-semibold text-gray-600">
                            {{ $product->category->name ?? '-' }}
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $product->unit_type === 'gram' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $product->unit_type }}
                            </span>
                            <p class="text-[11px] text-gray-500 mt-1 font-bold">{{ $product->stock }} {{ $product->unit_type }}</p>
                        </td>
                        <td class="p-4">
                            <p class="text-gray-400 text-[10px]">HPP: Rp {{ number_format($product->buy_price, 0, ',', '.') }}</p>
                            <p class="font-bold text-gray-900">Jual: Rp {{ number_format($product->sell_price, 0, ',', '.') }}</p>
                        </td>
                        <td class="p-4">
                            @if($product->is_consignment)
                                <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-[10px] font-bold">
                                    🤝 {{ $product->supplier->name ?? 'UMKM' }}
                                </span>
                            @else
                                <span class="text-gray-400 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Belum ada produk. Klik tombol tambah produk di atas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>