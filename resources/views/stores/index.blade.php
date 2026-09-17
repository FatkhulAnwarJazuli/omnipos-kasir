@extends('layouts.app')

@section('content')
<div x-data="{ openCreate: false, openEdit: false, editAction: '', editName: '' }" class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Toko</h2>
            <p class="text-xs text-slate-500">Kelola unit toko/brand multi-tenant dalam sistem.</p>
        </div>
        <button @click="openCreate = true" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3 text-center font-medium text-white hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-xs cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Toko Baru
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-sm">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Cards Toko -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($stores as $store)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-4">
                    @if($store->logo)
                        <img src="{{ asset('storage/' . $store->logo) }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center border border-indigo-100 text-lg">
                            {{ strtoupper(substr($store->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">{{ $store->name }}</h3>
                        <p class="text-xs text-slate-500 font-medium">{{ $store->products_count }} Produk Terdaftar</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button @click="openEdit = true; editAction = '{{ route('stores.update', $store->id) }}'; editName = '{{ $store->name }}';" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Edit</button>
                    <form action="{{ route('stores.destroy', $store->id) }}" method="POST" onsubmit="return confirm('Hapus toko ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50 rounded-lg transition">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400">Belum ada toko terdaftar.</div>
        @endforelse
    </div>

    <!-- MODAL CREATE (DIPAKSA KE BODY AGAR FULLSCREEN BLUR) -->
    <template x-teleport="body">
        <div x-show="openCreate" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openCreate = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Tambah Toko Baru</h3>
                    <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('stores.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Toko</label>
                        <input type="text" name="name" required placeholder="Contoh: Markasnack" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Logo Toko (Opsional)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700">
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs">Simpan Toko</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL EDIT (DIPAKSA KE BODY AGAR FULLSCREEN BLUR) -->
    <template x-teleport="body">
        <div x-show="openEdit" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4">
            <div @click.away="openEdit = false" class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 text-base">Edit Toko</h3>
                    <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="editAction" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Toko</label>
                        <input type="text" name="name" x-model="editName" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ganti Logo (Opsional)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700">
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection