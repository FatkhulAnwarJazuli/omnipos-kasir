@extends('layouts.app')

@section('content')
<div x-data="{ 
    openCreate: false, 
    openEdit: false, 
    editAction: '', 
    editStoreId: '', 
    editTitle: '', 
    editCategory: 'operasional', 
    editAmount: '', 
    editDate: '', 
    editNote: '' 
}" class="space-y-6">

    <!-- Header Page & Stats -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pengeluaran & Biaya Operasional</h2>
            <p class="text-xs text-slate-500">Pencatatan beban operasional, sewa tenda event, transportasi, dan biaya luar toko.</p>
        </div>
        <button @click="openCreate = true" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-5 py-3 text-center font-medium text-white hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-xs cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pengeluaran
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 text-sm">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Total Summary Card -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Operational Expense</span>
            <h3 class="text-2xl font-extrabold text-rose-600 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </div>

    <!-- Table Expenses -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Tanggal</th>
                        <th class="py-4 px-6">Toko</th>
                        <th class="py-4 px-6">Keterangan Pengeluaran</th>
                        <th class="py-4 px-6 text-center">Kategori</th>
                        <th class="py-4 px-6 text-right">Nominal (Rp)</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="py-4 px-6 whitespace-nowrap text-slate-600 font-mono text-xs">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 border border-indigo-100">
                                    {{ $expense->store->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800 whitespace-nowrap">
                                <div>{{ $expense->title }}</div>
                                @if($expense->note)
                                    <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $expense->note }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <span class="inline-block rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 uppercase">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-rose-600 whitespace-nowrap">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="
                                                openEdit = true; 
                                                editAction = '{{ route('expenses.update', $expense->id) }}';
                                                editStoreId = '{{ $expense->store_id }}';
                                                editTitle = '{{ $expense->title }}';
                                                editCategory = '{{ $expense->category }}';
                                                editAmount = '{{ $expense->amount }}';
                                                editDate = '{{ $expense->expense_date }}';
                                                editNote = '{{ $expense->note }}';
                                            " 
                                            class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                                            title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">Belum ada catatan pengeluaran.</td>
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
                    <h3 class="font-bold text-slate-900 text-base">Tambah Pengeluaran Operasional</h3>
                    <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form action="{{ route('expenses.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Toko</label>
                        <select name="store_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm bg-white">
                            <option value="">-- Pilih Toko --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Pengeluaran</label>
                        <input type="text" name="title" required placeholder="Contoh: Sewa Tenda Bazaar Expo" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm bg-white">
                                <option value="tenda">Sewa Tenda</option>
                                <option value="transportasi">Transportasi</option>
                                <option value="operasional">Operasional</option>
                                <option value="konsumsi">Konsumsi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required placeholder="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="note" rows="2" placeholder="Keterangan rincian event/transaksi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="openCreate = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-medium shadow-md">Simpan Catatan</button>
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
                    <h3 class="font-bold text-slate-900 text-base">Edit Pengeluaran</h3>
                    <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form :action="editAction" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Toko</label>
                        <select name="store_id" x-model="editStoreId" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm bg-white">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Pengeluaran</label>
                        <input type="text" name="title" x-model="editTitle" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category" x-model="editCategory" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm bg-white">
                                <option value="tenda">Sewa Tenda</option>
                                <option value="transportasi">Transportasi</option>
                                <option value="operasional">Operasional</option>
                                <option value="konsumsi">Konsumsi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date" name="expense_date" x-model="editDate" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" x-model="editAmount" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="note" x-model="editNote" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm"></textarea>
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