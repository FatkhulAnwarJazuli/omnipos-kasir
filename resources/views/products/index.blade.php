@extends('layouts.app')

@section('content')
<div x-data="productManagement()" class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Produk & Stok FIFO</h2>
            <p class="text-xs text-slate-500">Kelola katalog barang, template harga, restok simpel, dan stock opname.</p>
        </div>
        <button @click="openCreate = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk Baru
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-xs font-medium">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Alert Error -->
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 text-xs font-medium">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 text-xs font-medium">
            <p class="font-bold mb-1">Data belum tersimpan, mohon periksa:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Bar Lengkap -->
    <form method="GET" action="{{ route('products.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            
            <!-- 1. Search Nama Produk -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Produk</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama produk..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- 2. Filter Toko -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Filter Toko</label>
                <select name="store_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="">Semua Toko</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Filter Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Filter Kategori</label>
                <select name="category_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Filter Satuan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan</label>
                <select name="unit_type" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="">Semua Satuan</option>
                    <option value="gram" {{ $unitType == 'gram' ? 'selected' : '' }}>Gram (Gramasi)</option>
                    <option value="pcs" {{ $unitType == 'pcs' ? 'selected' : '' }}>Pcs (Satuan)</option>
                    <option value="ml" {{ $unitType == 'ml' ? 'selected' : '' }}>ML (Cairan)</option>
                </select>
            </div>

            <!-- 5. Filter Status Stok -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Stok</label>
                <select name="stock_status" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="">Semua Status</option>
                    <option value="available" {{ $stockStatus == 'available' ? 'selected' : '' }}>Aman (> 5)</option>
                    <option value="low" {{ $stockStatus == 'low' ? 'selected' : '' }}>Menipis (1 - 5)</option>
                    <option value="empty" {{ $stockStatus == 'empty' ? 'selected' : '' }}>Habis (0)</option>
                </select>
            </div>

        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <!-- 6. Sorting / Urutkan -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Urutkan:</span>
                <select name="sort_by" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white font-semibold text-slate-700">
                    <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                    <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>Nama (Z - A)</option>
                    <option value="stock_desc" {{ $sortBy == 'stock_desc' ? 'selected' : '' }}>Stok Terbanyak</option>
                    <option value="stock_asc" {{ $sortBy == 'stock_asc' ? 'selected' : '' }}>Stok Tersedikit</option>
                </select>
            </div>

            <!-- 7. Reset Filter -->
            <a href="{{ route('products.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1">
                🔄 Reset Filter
            </a>
        </div>
    </form>

    <!-- Table Product List -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                        <th class="py-3.5 px-6">Produk</th>
                        <th class="py-3.5 px-6">Toko & Kategori</th>
                        <th class="py-3.5 px-6">Supplier</th>
                        <th class="py-3.5 px-6">Satuan</th>
                        <th class="py-3.5 px-6 text-center">Harga Jual / Template</th>
                        <th class="py-3.5 px-6 text-center">Total Stok</th>
                        <th class="py-3.5 px-6 text-center">Aksi & Inventaris</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($products as $product)
                        @php
                            $totalStock = (int) $product->batches->sum('current_stock');
                            $templates = $product->price_templates ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-slate-400 font-bold text-xs">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">{{ $product->name }}</h4>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ count($product->batches) }} Batch Aktif</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="space-y-0.5">
                                    <span class="inline-block rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 border border-indigo-100">
                                        {{ $product->store->name ?? '-' }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">{{ $product->category->name ?? 'Tanpa Kategori' }}</div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($product->supplier)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200">
                                        📦 {{ $product->supplier->name }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 font-mono uppercase text-slate-600 font-semibold whitespace-nowrap">
                                {{ $product->unit_type }}
                            </td>
                            
                            <!-- Harga Jual / Template -->
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                @if($product->unit_type === 'pcs')
                                    <span class="inline-block px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-xl font-bold border border-emerald-200/60">
                                        Rp {{ number_format($product->sell_price, 0, ',', '.') }} / pcs
                                    </span>
                                @else
                                    <button type="button" @click="openTemplate({{ $product->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl font-bold transition border border-indigo-200/60 cursor-pointer">
                                        🏷️ {{ count($templates) }} Template Harga
                                    </button>
                                @endif
                            </td>

                            <!-- Total Stok -->
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $totalStock > 5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ number_format($totalStock, 0, ',', '.') }} {{ $product->unit_type }}
                                </span>
                            </td>

                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openBatch({{ $product->id }})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] transition cursor-pointer">
                                        🔍 Detail Batch
                                    </button>
                                    <button type="button" @click="addBatchAction = '{{ route('products.add-batch', $product->id) }}'; openAddBatchModal = true" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg text-[11px] transition border border-emerald-200/60 cursor-pointer">
                                        + Restok
                                    </button>
                                    <button type="button" @click="openOpname({{ $product->id }})" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-lg text-[11px] transition border border-amber-200/60 cursor-pointer">
                                        ⚖️ Opname
                                    </button>
                                    <button type="button" @click="
                                        openEdit = true;
                                        editAction = '{{ route('products.update', $product->id) }}';
                                        editName = @js($product->name);
                                        editStoreId = '{{ $product->store_id }}';
                                        editCategoryId = '{{ $product->category_id }}';
                                        editSupplierId = '{{ $product->supplier_id ?? '' }}';
                                        editUnitType = '{{ $product->unit_type }}';
                                        editSellPrice = '{{ (int) $product->sell_price }}';
                                    " class="px-2 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg font-bold text-[11px] cursor-pointer">Edit</button>
                                    
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk {{ $product->name }}?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 text-rose-600 hover:bg-rose-50 rounded-lg font-bold text-[11px] transition cursor-pointer">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">Belum ada data produk. Klik "Tambah Produk Baru" untuk memulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL BATCH TRANSPARANSI -->
    <template x-teleport="body">
        <div x-show="openBatchModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openBatchModal = false" class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <div class="flex justify-between items-center border-b pb-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base" x-text="selectedProduct ? selectedProduct.name : ''"></h3>
                        <p class="text-xs text-slate-400">Transparansi Riwayat Stok FIFO & Modal HPP</p>
                    </div>
                    <button type="button" @click="openBatchModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <div class="max-h-80 overflow-y-auto border rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b">
                            <tr>
                                <th class="p-3">Kode Batch</th>
                                <th class="p-3 text-right">Modal HPP / Satuan (Rp)</th>
                                <th class="p-3 text-center">Stok Awal / Sisa</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-if="selectedProduct && selectedProduct.batches">
                                <template x-for="batch in selectedProduct.batches" :key="batch.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-3 font-mono text-slate-600" x-text="batch.batch_code || ('#' + batch.id)"></td>
                                        <td class="p-3 text-right font-mono text-slate-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(batch.buy_price)"></td>
                                        <td class="p-3 text-center font-bold" x-text="new Intl.NumberFormat('id-ID').format(batch.initial_stock) + ' / ' + new Intl.NumberFormat('id-ID').format(batch.current_stock)"></td>
                                        <td class="p-3 text-center">
                                            <span x-show="batch.current_stock > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Aktif FIFO</span>
                                            <span x-show="batch.current_stock <= 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-400">Habis</span>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <button type="button" @click="openBatchModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>
    </template>

    <!-- MODAL TEMPLATE HARGA JUAL -->
    <template x-teleport="body">
        <div x-show="openTemplateModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openTemplateModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <div class="flex justify-between items-center border-b pb-2">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm" x-text="selectedProduct ? selectedProduct.name : ''"></h3>
                        <p class="text-[11px] text-slate-400">Atur Varian Porsi & Harga Jual Resmi POS</p>
                    </div>
                    <button type="button" @click="openTemplateModal = false" class="text-slate-400 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form :action="templateAction" method="POST" class="space-y-3">
                    @csrf
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-[11px] text-slate-400 self-center">Isi cepat:</span>
                        <button type="button" @click="applyPreset('mix', 'edit')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Mix Varian</button>
                        <button type="button" @click="applyPreset('kulit', 'edit')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Kulit &amp; Usus</button>
                        <button type="button" @click="applyPreset('bucket', 'edit')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Bucket 1L</button>
                    </div>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <template x-for="(tpl, idx) in templates" :key="idx">
                            <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <input type="number" :name="'price_templates['+idx+'][amount]'" x-model="tpl.amount" placeholder="Porsi/Gram (cth: 250)" required class="w-1/2 px-2 py-1.5 border rounded-lg text-xs font-bold bg-white">
                                <input type="number" :name="'price_templates['+idx+'][price]'" x-model="tpl.price" placeholder="Harga Rp (cth: 22000)" required class="w-1/2 px-2 py-1.5 border rounded-lg text-xs font-bold bg-white">
                                <button type="button" @click="removeTemplateRow(idx)" class="text-rose-500 font-bold px-1 cursor-pointer">&times;</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addTemplateRow()" class="w-full py-2 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-xl hover:bg-indigo-100 border border-indigo-200 cursor-pointer">
                        + Tambah Baris Varian Harga
                    </button>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="openTemplateModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL RESTOK SIMPEL -->
    <template x-teleport="body">
        <div x-show="openAddBatchModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openAddBatchModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <h3 class="font-bold text-slate-900 text-sm">Restok Batch Baru</h3>
                <form :action="addBatchAction" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tambah Stok (Pcs / Gram / ML)</label>
                        <input type="number" step="any" name="initial_stock" required placeholder="Contoh: 5000 (untuk 5 kg)" class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Uang Belanja / Modal Beli (Rp)</label>
                        <input type="number" name="total_buy_price" required placeholder="Contoh: 200000 (Total pengeluaran)" class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openAddBatchModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Restok</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL STOCK OPNAME -->
    <template x-teleport="body">
        <div x-show="openOpnameModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openOpnameModal = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Stock Opname (Penyesuaian Fisik)</h3>
                    <p class="text-[11px] text-slate-400" x-text="selectedProduct ? selectedProduct.name : ''"></p>
                </div>
                <form :action="opnameAction" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hasil Timbangan / Fisik Asli di Toko</label>
                        <input type="number" step="any" name="physical_stock" required placeholder="Masukkan jumlah fisik saat ini" class="w-full px-3 py-2 border rounded-xl text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penyesuaian</label>
                        <select name="reason" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="Susut Ditimbang">Susut Ditimbang</option>
                            <option value="Sampel / Tester">Sampel / Tester Promo</option>
                            <option value="Tumpah / Rusak">Tumpah / Barang Rusak</option>
                            <option value="Selisih Hitung">Selisih Hitung Pembukuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-xl text-xs" placeholder="Keterangan singkat..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openOpnameModal = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Opname</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL CREATE PRODUCT -->
    <template x-teleport="body">
        <div x-show="openCreate" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openCreate = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <h3 class="font-bold text-slate-900 text-sm">Tambah Produk Baru</h3>
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Toko</label>
                            <select name="store_id" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category_id" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Pemasok Konsinyasi (Opsional)</label>
                        <select name="supplier_id" class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="">-- Tanpa Supplier (Milik Sendiri) --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" required placeholder="Contoh: Basreng Stik Ori" class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Produk</label>
                            <select name="unit_type" x-model="createUnit" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                <option value="gram">Gram (Kiloan/Gramasi)</option>
                                <option value="pcs">Pcs (Satuan)</option>
                                <option value="ml">ML (Cairan)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Produk</label>
                            <input type="file" name="image" accept="image/*" class="w-full px-2 py-1.5 border rounded-xl text-xs">
                        </div>
                    </div>
                    <!-- Harga Jual -->
                    <div class="border-t pt-3">
                        <template x-if="createUnit === 'pcs'">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Satuan (Rp)</label>
                                <input type="number" name="sell_price" min="0" required placeholder="Contoh: 15000" class="w-full px-3 py-2 border rounded-xl text-xs">
                            </div>
                        </template>
                        <template x-if="createUnit !== 'pcs'">
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">Template Harga Jual (Porsi &rarr; Harga)</label>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="text-[11px] text-slate-400 self-center">Isi cepat:</span>
                                    <button type="button" @click="applyPreset('mix', 'create')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Mix Varian</button>
                                    <button type="button" @click="applyPreset('kulit', 'create')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Kulit &amp; Usus</button>
                                    <button type="button" @click="applyPreset('bucket', 'create')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] rounded-lg border border-indigo-200 cursor-pointer">Bucket 1L</button>
                                </div>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    <template x-for="(tpl, idx) in createTemplates" :key="idx">
                                        <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-200">
                                            <input type="number" :name="'price_templates['+idx+'][amount]'" x-model="tpl.amount" placeholder="Porsi (cth: 250)" required class="w-1/2 px-2 py-1.5 border rounded-lg text-xs font-bold bg-white">
                                            <input type="number" :name="'price_templates['+idx+'][price]'" x-model="tpl.price" placeholder="Harga Rp (cth: 22000)" required class="w-1/2 px-2 py-1.5 border rounded-lg text-xs font-bold bg-white">
                                            <button type="button" @click="createTemplates.splice(idx, 1)" class="text-rose-500 font-bold px-1 cursor-pointer">&times;</button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="createTemplates.push({ amount: '', price: '' })" class="w-full py-2 bg-slate-50 text-slate-600 font-bold text-xs rounded-xl hover:bg-slate-100 border border-slate-200 cursor-pointer">
                                    + Tambah Baris Harga
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-3 border-t pt-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Awal (Pcs/Gram/ML)</label>
                            <input type="number" step="any" name="initial_stock" required placeholder="5000" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Total Modal Beli (Rp)</label>
                            <input type="number" name="total_buy_price" required placeholder="200000" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT PRODUCT -->
    <template x-teleport="body">
        <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openEdit = false" class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 space-y-4 my-auto">
                <h3 class="font-bold text-slate-900 text-sm">Edit Informasi Produk</h3>
                <form :action="editAction" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Toko</label>
                            <select name="store_id" x-model="editStoreId" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category_id" x-model="editCategoryId" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Pemasok Konsinyasi (Opsional)</label>
                        <select name="supplier_id" x-model="editSupplierId" class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="">-- Tanpa Supplier (Milik Sendiri) --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="editName" required class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Produk</label>
                            <select name="unit_type" x-model="editUnitType" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                                <option value="gram">Gram (Kiloan/Gramasi)</option>
                                <option value="pcs">Pcs (Satuan)</option>
                                <option value="ml">ML (Cairan)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" accept="image/*" class="w-full px-2 py-1.5 border rounded-xl text-xs">
                        </div>
                    </div>
                    <template x-if="editUnitType === 'pcs'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Satuan (Rp)</label>
                            <input type="number" name="sell_price" x-model="editSellPrice" min="0" required class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                    </template>
                    <template x-if="editUnitType !== 'pcs'">
                        <p class="text-[11px] text-slate-400">Harga per porsi diatur lewat tombol <b>Template Harga</b> di tabel produk.</p>
                    </template>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
function productManagement() {
    return {
        openCreate: false, 
        openEdit: false, 
        openBatchModal: false, 
        openAddBatchModal: false,
        openTemplateModal: false,
        openOpnameModal: false,
        selectedProduct: null,
        products: @json($products),
        editAction: '', 
        editName: '', 
        editStoreId: '', 
        editCategoryId: '', 
        editSupplierId: '',
        editUnitType: '',
        editSellPrice: '',
        createUnit: 'gram',
        createTemplates: [],
        addBatchAction: '',
        templateAction: '',
        opnameAction: '',
        templates: [],

        // Preset template harga sesuai price list Markasnack
        presets: {
            mix:    [{amount:50,price:6000},{amount:100,price:10000},{amount:200,price:20000},{amount:250,price:22000},{amount:500,price:40000},{amount:1000,price:75000}],
            kulit:  [{amount:50,price:7000},{amount:100,price:13000},{amount:200,price:25000},{amount:250,price:30000},{amount:500,price:58000},{amount:1000,price:108000}],
            bucket: [{amount:1000,price:30000}]
        },

        applyPreset(key, target) {
            const rows = this.presets[key].map(r => ({ ...r }));
            if (target === 'create') { this.createTemplates = rows; } else { this.templates = rows; }
        },

        openBatch(id) {
            this.selectedProduct = this.products.find(p => p.id === id);
            this.openBatchModal = true;
        },

        openTemplate(id) {
            this.selectedProduct = this.products.find(p => p.id === id);
            this.templates = (this.selectedProduct && this.selectedProduct.price_templates) ? [...this.selectedProduct.price_templates] : [];
            this.templateAction = '/products/' + id + '/update-templates';
            this.openTemplateModal = true;
        },

        openOpname(id) {
            this.selectedProduct = this.products.find(p => p.id === id);
            this.opnameAction = '/products/' + id + '/stock-opname';
            this.openOpnameModal = true;
        },

        addTemplateRow() { 
            this.templates.push({ amount: '', price: '' }); 
        },

        removeTemplateRow(index) { 
            this.templates.splice(index, 1); 
        }
    };
}
</script>
@endsection