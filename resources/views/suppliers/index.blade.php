@extends('layouts.app')

@section('content')
<div x-data="{ 
    openCreate: false, 
    openEdit: false, 
    editAction: '', 
    editName: '', 
    editPhone: '', 
    editSuppliedItems: '', 
    editNotes: '' 
}" class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Supplier / Pemasok</h2>
            <p class="text-xs text-slate-500">Pencatatan mitra UMKM & supplier barang titipan (konsinyasi).</p>
        </div>
        <button @click="openCreate = true" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3 text-center font-medium text-white hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-xs cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Supplier Baru
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-sm">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Table Suppliers -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Nama Supplier</th>
                        <th class="py-4 px-6">No. Telepon / WhatsApp</th>
                        <th class="py-4 px-6">Barang yang Disuplai</th>
                        <th class="py-4 px-6 text-center">Total Produk</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="py-4 px-6 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $supplier->name }}
                                @if($supplier->notes)
                                    <p class="text-xs text-slate-400 font-normal mt-0.5">{{ $supplier->notes }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-mono text-xs whitespace-nowrap">
                                {{ $supplier->phone ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                {{ $supplier->supplied_items ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap font-bold text-slate-800">
                                {{ $supplier->products_count }} Produk
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="
                                                openEdit = true; 
                                                editAction = '{{ route('suppliers.update', $supplier->id) }}';
                                                editName = '{{ $supplier->name }}';
                                                editPhone = '{{ $supplier->phone }}';
                                                editSuppliedItems = '{{ $supplier->supplied_items }}';
                                                editNotes = '{{ $supplier->notes }}';
                                            " 
                                            class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Hapus supplier ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">Belum ada data supplier terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL CREATE -->
    <template x-teleport="body">
        <div x-show="openCreate" x-cloak 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openCreate = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Tambah Supplier Baru</h3>
                    <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('suppliers.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Supplier / UMKM</label>
                        <input type="text" name="name" required placeholder="Contoh: Dapur Bu Titin / Keripik Pedas Banjar" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                        <input type="text" name="phone" placeholder="081234567890" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Barang yang Disuplai / Titipan</label>
                        <textarea name="supplied_items" rows="2" placeholder="Contoh: Keripik Pisang, Basreng, Usus Crispy..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Perjanjian Komisi</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Bagi hasil 80% Supplier - 20% Toko..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Simpan Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT -->
    <template x-teleport="body">
        <div x-show="openEdit" x-cloak 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openEdit = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Edit Supplier</h3>
                    <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="editAction" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Supplier / UMKM</label>
                        <input type="text" name="name" x-model="editName" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                        <input type="text" name="phone" x-model="editPhone" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Barang yang Disuplai / Titipan</label>
                        <textarea name="supplied_items" x-model="editSuppliedItems" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Perjanjian Komisi</label>
                        <textarea name="notes" x-model="editNotes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection