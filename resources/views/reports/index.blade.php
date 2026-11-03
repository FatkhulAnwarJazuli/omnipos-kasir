@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page & Tombol Ekspor -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Penjualan & Laba Rugi</h2>
            <p class="text-xs text-slate-500">Rekapitulasi omzet, modal HPP FIFO, beban operasional, dan laba bersih per toko.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export-excel', request()->all()) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-center font-bold text-white hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20 text-xs">
                <span>📊</span> Ekspor Excel (.csv)
            </a>
            <a href="{{ route('reports.print', request()->all()) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-center font-bold text-white hover:bg-slate-900 transition shadow-md shadow-slate-900/20 text-xs">
                <span>🖨️</span> Cetak / Simpan PDF
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('reports.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Periode</label>
                <select name="period" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Kustom Tanggal</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Toko</label>
                <select name="store_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white">
                    <option value="">Semua Toko</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs">
            </div>

            <div>
                <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 transition">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Ringkasan Laba Rugi (Profit & Loss Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Omzet Penjualan -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Omzet Penjualan</span>
            <h3 class="text-xl font-extrabold text-slate-900">Rp {{ number_format($netSales, 0, ',', '.') }}</h3>
            <p class="text-[10px] text-slate-400">
                Kotor: Rp {{ number_format($grossSales, 0, ',', '.') }} 
                @if($totalDiscount > 0)
                    | Disc: -Rp {{ number_format($totalDiscount, 0, ',', '.') }}
                @endif
            </p>
        </div>

        <!-- HPP Modal FIFO -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">HPP Modal (FIFO)</span>
            <h3 class="text-xl font-extrabold text-amber-600">Rp {{ number_format($cogs, 0, ',', '.') }}</h3>
            <p class="text-[10px] text-slate-400">Modal produk terjual</p>
        </div>

        <!-- Operasional -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Beban Operasional</span>
            <h3 class="text-xl font-extrabold text-rose-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
            <p class="text-[10px] text-slate-400">Sewa, komisi, transport</p>
        </div>

        <!-- Laba Bersih -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-1">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Laba Bersih (Net Profit)</span>
            <h3 class="text-xl font-extrabold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </h3>
            <p class="text-[10px] text-slate-400">Laba kotor - operasional</p>
        </div>
    </div>

    <!-- Tabel Rincian Transaksi Terjual -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 text-sm">Rincian Barang Terjual</h3>
            <span class="text-xs text-slate-400">{{ count($transactionDetails) }} Item Terjual</span>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">No. Struk</th>
                        <th class="py-3.5 px-6">Toko</th>
                        <th class="py-3.5 px-6">Nama Produk</th>
                        <th class="py-3.5 px-6 text-center">Qty</th>
                        <th class="py-3.5 px-6 text-right">Modal HPP (Rp)</th>
                        <th class="py-3.5 px-6 text-right">Harga Jual (Rp)</th>
                        <th class="py-3.5 px-6 text-right">Subtotal (Rp)</th>
                        <th class="py-3.5 px-6 text-right">Laba Kotor (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transactionDetails as $detail)
                        @php
                            $detailCogs = $detail->quantity * $detail->buy_price;
                            $detailProfit = ($detail->subtotal - $detail->discount_amount) - $detailCogs;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-6 font-mono text-slate-500">
                                {{ $detail->transaction->invoice_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 border border-indigo-100">
                                    {{ $detail->store->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-bold text-slate-800">
                                {{ $detail->product->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-center font-bold text-slate-700">
                                {{ number_format($detail->quantity) }}
                            </td>
                            <td class="py-3.5 px-6 text-right text-slate-500 font-mono">
                                Rp {{ number_format($detail->buy_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-6 text-right text-slate-700 font-mono">
                                Rp {{ number_format($detail->sell_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-6 text-right font-bold text-slate-900 font-mono">
                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                @if($detail->discount_amount > 0)
                                    <span class="block text-[10px] text-emerald-600 font-normal">- Disc {{ number_format($detail->discount_amount) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right font-bold text-emerald-600 font-mono">
                                Rp {{ number_format($detailProfit, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection