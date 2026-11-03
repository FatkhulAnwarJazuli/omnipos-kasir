<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - {{ $start->format('d/m/Y') }} s/d {{ $end->format('d/m/Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 text-xs font-sans p-6 print:bg-white print:p-0">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-lg print:shadow-none print:max-w-full">
        
        <!-- Header Dokumen Cetak -->
        <div class="flex justify-between items-start border-b pb-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-wider">MARKASNACK & GLYTCH</h1>
                <p class="text-slate-500 font-medium text-xs">Laporan Penjualan & Laba Rugi Perusahaan</p>
            </div>
            <div class="text-right">
                <span class="text-slate-400 text-[10px] block">PERIODE LAPORAN</span>
                <span class="font-bold text-slate-800">{{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</span>
            </div>
        </div>

        <!-- Action Bar Cetak -->
        <div class="no-print flex justify-end gap-2 mb-6">
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>

        <!-- Ringkasan Finansial -->
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="border rounded-xl p-3 bg-slate-50">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Omzet Bersih</span>
                <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($netSales, 0, ',', '.') }}</span>
            </div>
            <div class="border rounded-xl p-3 bg-slate-50">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total HPP (FIFO)</span>
                <span class="text-sm font-extrabold text-amber-600">Rp {{ number_format($cogs, 0, ',', '.') }}</span>
            </div>
            <div class="border rounded-xl p-3 bg-slate-50">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Beban Operasional</span>
                <span class="text-sm font-extrabold text-rose-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
            </div>
            <div class="border rounded-xl p-3 bg-slate-50">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Laba Bersih</span>
                <span class="text-sm font-extrabold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Tabel Rincian Data -->
        <table class="w-full text-left border-collapse border text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-slate-700 font-bold border-b">
                    <th class="p-2.5 border-r">No. Struk</th>
                    <th class="p-2.5 border-r">Waktu</th>
                    <th class="p-2.5 border-r">Toko</th>
                    <th class="p-2.5 border-r">Nama Produk</th>
                    <th class="p-2.5 text-center border-r">Qty</th>
                    <th class="p-2.5 text-right border-r">Modal HPP</th>
                    <th class="p-2.5 text-right border-r">Harga Jual</th>
                    <th class="p-2.5 text-right border-r">Subtotal</th>
                    <th class="p-2.5 text-right">Laba Kotor</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($transactionDetails as $detail)
                    @php
                        $detailCogs = $detail->quantity * $detail->buy_price;
                        $detailProfit = ($detail->subtotal - $detail->discount_amount) - $detailCogs;
                    @endphp
                    <tr>
                        <td class="p-2 border-r font-mono text-slate-600">{{ $detail->transaction->invoice_number ?? '-' }}</td>
                        <td class="p-2 border-r font-mono text-slate-500 text-[10px]">{{ $detail->created_at ? $detail->created_at->format('d/m/y H:i') : '-' }}</td>
                        <td class="p-2 border-r font-medium">{{ $detail->store->name ?? '-' }}</td>
                        <td class="p-2 border-r font-bold text-slate-800">{{ $detail->product->name ?? '-' }}</td>
                        <td class="p-2 text-center border-r font-bold">{{ number_format($detail->quantity) }}</td>
                        <td class="p-2 text-right border-r font-mono">Rp {{ number_format($detail->buy_price, 0, ',', '.') }}</td>
                        <td class="p-2 text-right border-r font-mono">Rp {{ number_format($detail->sell_price, 0, ',', '.') }}</td>
                        <td class="p-2 text-right border-r font-mono font-bold">Rp {{ number_format($detail->subtotal - $detail->discount_amount, 0, ',', '.') }}</td>
                        <td class="p-2 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($detailProfit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-400">Tidak ada transaksi terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Cetak -->
        <div class="mt-12 flex justify-between items-end text-[11px] text-slate-500">
            <div>
                <p>Dicetak pada: {{ date('d/m/Y H:i') }} WITA</p>
                <p>Sistem Management OmniPOS</p>
            </div>
            <div class="text-center w-48">
                <p class="mb-12">Penanggung Jawab Toko,</p>
                <p class="font-bold text-slate-800 border-b border-slate-400 pb-1">( ........................................ )</p>
            </div>
        </div>

    </div>

</body>
</html>