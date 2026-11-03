@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col gap-1">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Overview</h2>
        <p class="text-xs text-slate-500">Ringkasan performa bisnis multi-store & statistik penjualan real-time.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex justify-between items-center">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Penjualan Hari Ini</span>
                <h3 class="text-xl font-extrabold text-indigo-600 mt-1">Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $todayTransactionsCount }} Transaksi hari ini</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex justify-between items-center">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Laba Bersih Bulan Ini</span>
                <h3 class="text-xl font-extrabold {{ $monthlyNetProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                    Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}
                </h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Setelah terpotong modal & operasional</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex justify-between items-center">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Omzet Bulan Ini</span>
                <h3 class="text-xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($monthlySales, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Total gross sales</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex justify-between items-center">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Toko Aktif</span>
                <h3 class="text-xl font-extrabold text-indigo-600 mt-1">{{ $totalStores }} Toko</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Sistem Multi-tenant active</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>
    </div>

    <!-- Grafik & Top Products Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart Grafik Tren (8 Col) -->
        <div class="lg:col-span-8 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Tren Penjualan 7 Hari Terakhir</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Produk Terlaris (4 Col) -->
        <div class="lg:col-span-4 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Top 5 Produk Terlaris</h3>
            <div class="space-y-3">
                @forelse($topProducts as $item)
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 text-xs">{{ $item->product->name ?? '-' }}</h5>
                            <span class="text-[10px] text-slate-400">{{ $item->product->store->name ?? '-' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-indigo-600 block">{{ number_format($item->total_qty) }} Terjual</span>
                            <span class="text-[10px] text-slate-400">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-8 text-xs text-slate-400">Belum ada data penjualan bulan ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Peringatan Stok Kritis Batch -->
    @if(count($lowStockBatches) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Peringatan Stok Batch Kritis (&le; 5 Unit)
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockBatches as $batch)
                    <div class="bg-white p-3 rounded-xl border border-amber-200/60 shadow-xs flex justify-between items-center">
                        <div>
                            <h5 class="font-bold text-slate-800 text-xs">{{ $batch->product->name ?? '-' }}</h5>
                            <span class="text-[10px] text-slate-400">{{ $batch->product->store->name ?? '-' }} • Batch: {{ $batch->batch_code ?? '#' . $batch->id }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-rose-100 text-rose-700 font-bold rounded-lg text-xs">
                            Sisa {{ number_format($batch->current_stock) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Penjualan (Rp)',
                data: @json($chartData),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endsection