@extends('layouts.app')

@section('content')
<div x-data="{ 
    openCreate: false, 
    openEdit: false, 
    openBatch: false,
    editAction: '',
    batchAction: '',
    editStoreId: '',
    editCategoryId: '',
    editSupplierId: '',
    editName: '',
    editUnitType: '',
    editConsignment: false,
    productName: ''
}" class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Stok & Katalog Produk</h2>
            <p class="text-xs text-slate-500">Daftar barang dan rincian sisa stok per batch FIFO.</p>
        </div>
        <button @click="openCreate = true" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3 text-center font-medium text-white hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-xs cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk Baru
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-sm">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Table Product -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Toko / Brand</th>
                        <th class="py-4 px-6">Nama Produk</th>
                        <th class="py-4 px-6">Kategori</th>
                        <th class="py-4 px-6">Satuan</th>
                        <th class="py-4 px-6">Total Stok</th>
                        <th class="py-4 px-6 text-center">Tipe</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 border border-indigo-100">
                                    {{ $product->store->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $product->name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                {{ $product->category->name ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 font-mono text-xs whitespace-nowrap uppercase">
                                {{ $product->unit_type }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-900 whitespace-nowrap">
                                {{ number_format($product->batches->sum('current_stock')) }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                @if($product->is_consignment)
                                    <span class="inline-block rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 border border-amber-200">Titipan UMKM</span>
                                @else
                                    <span class="inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">Milik Sendiri</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openBatch = true; batchAction = '{{ route('products.store-batch', $product->id) }}'; productName = '{{ $product->name }}';" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                        + Batch
                                    </button>

                                    <button @click="
                                        openEdit = true;
                                        editAction = '{{ route('products.update', $product->id) }}';
                                        editStoreId = '{{ $product->store_id }}';
                                        editCategoryId = '{{ $product->category_id }}';
                                        editSupplierId = '{{ $product->supplier_id }}';
                                        editName = '{{ $product->name }}';
                                        editUnitType = '{{ $product->unit_type }}';
                                        editConsignment = {{ $product->is_consignment ? 'true' : 'false' }};
                                    " class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini beserta seluruh stok batch-nya?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-slate-400">Belum ada produk terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL CREATE PRODUCT (TELEPORT + TRANSITION) -->
    <template x-teleport="body">
        <div x-show="openCreate" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            
            <div @click.away="openCreate = false" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden my-8">
                
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Tambah Produk Baru</h3>
                    <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Toko</label>
                            <select name="store_id" required class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                <option value="">-- Pilih Toko --</option>
                                @foreach($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category_id" required class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category) <option value="{{ $category->id }}">{{ $category->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" required placeholder="Contoh: Keripik Singkong" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Unit</label>
                            <select name="unit_type" required class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                <option value="pcs">Pcs (Satuan)</option>
                                <option value="gram">Gram (Timbangan)</option>
                                <option value="bucket">Bucket</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier (Opsional)</label>
                            <select name="supplier_id" class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                <option value="">-- Mandiri / Tanpa Supplier --</option>
                                @foreach($suppliers as $supplier) <option value="{{ $supplier->id }}">{{ $supplier->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Stok Batch Perdana</span>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" name="buy_price" placeholder="HPP Beli" required class="px-3 py-1.5 border rounded-lg text-xs bg-white">
                            <input type="number" name="sell_price" placeholder="Harga Jual" required class="px-3 py-1.5 border rounded-lg text-xs bg-white">
                            <input type="number" name="initial_stock" placeholder="Jumlah Stok" required class="px-3 py-1.5 border rounded-lg text-xs bg-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT PRODUCT (TELEPORT + TRANSITION) -->
    <template x-teleport="body">
        <div x-show="openEdit" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            
            <div @click.away="openEdit = false" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Edit Produk</h3>
                    <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="editAction" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Toko</label>
                            <select name="store_id" x-model="editStoreId" required class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                @foreach($stores as $store) <option value="{{ $store->id }}">{{ $store->name }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category_id" x-model="editCategoryId" required class="w-full px-3 py-2 border rounded-xl text-sm bg-white">
                                @foreach($categories as $category) <option value="{{ $category->id }}">{{ $category->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="editName" required class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL ADD BATCH FIFO (TELEPORT + TRANSITION) -->
    <template x-teleport="body">
        <div x-show="openBatch" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            
            <div @click.away="openBatch = false" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">+ Pasokan Batch Stok</h3>
                        <p class="text-xs text-slate-500" x-text="productName"></p>
                    </div>
                    <button @click="openBatch = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="batchAction" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Batch / Faktur</label>
                        <input type="text" name="batch_code" value="BATCH-{{ strtoupper(substr(uniqid(), -5)) }}" required class="w-full px-3 py-2 border rounded-xl text-sm font-mono">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">HPP Beli (Rp)</label>
                            <input type="number" name="buy_price" required placeholder="0" class="w-full px-3 py-2 border rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual (Rp)</label>
                            <input type="number" name="sell_price" required placeholder="0" class="w-full px-3 py-2 border rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Pasokan Stok</label>
                        <input type="number" name="initial_stock" required placeholder="0" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openBatch = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Tambah Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection