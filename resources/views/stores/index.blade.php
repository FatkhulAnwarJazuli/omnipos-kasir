@extends('layouts.app')

@section('content')
<div x-data="{ 
    openCreate: false, 
    openEdit: false, 
    editAction: '', 
    editName: '' 
}" class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Toko</h2>
            <p class="text-xs text-slate-500">Kelola daftar unit usaha dan brand toko.</p>
        </div>
        <button @click="openCreate = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-indigo-700 transition shadow-md shadow-indigo-500/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Toko Baru
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-xs font-medium">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Cards Store List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($stores as $store)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-3 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="inline-block rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-100 uppercase font-mono">
                            {{ $store->code ?? 'STORE' }}
                        </span>
                        <span class="text-[11px] font-bold text-slate-400">
                            {{ $store->products_count }} Produk
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mt-2">{{ $store->name }}</h3>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button @click="
                                openEdit = true; 
                                editAction = '{{ route('stores.update', $store->id) }}';
                                editName = '{{ $store->name }}';
                            " 
                            class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                        Edit
                    </button>
                    <form action="{{ route('stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('Hapus toko ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl p-12 text-center text-slate-400 text-xs border border-slate-200/80">
                Belum ada toko yang terdaftar.
            </div>
        @endforelse
    </div>

    <!-- MODAL CREATE (CUMA NAMA TOKO) -->
    <template x-teleport="body">
        <div x-show="openCreate" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openCreate = false" class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden my-auto">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-sm">Tambah Toko Baru</h3>
                    <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('stores.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Brand</label>
                        <input type="text" name="name" required placeholder="Contoh: Markasnack / Glytch" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium text-slate-700">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md">Simpan Toko</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT -->
    <template x-teleport="body">
        <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 overflow-y-auto">
            <div @click.away="openEdit = false" class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden my-auto">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-sm">Edit Nama Toko</h3>
                    <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="editAction" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Brand</label>
                        <input type="text" name="name" x-model="editName" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-medium text-slate-700">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection