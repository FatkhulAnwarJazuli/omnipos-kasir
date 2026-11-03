<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\TransactionDetail;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        return view('reports.index', $data);
    }

    // Ekspor Excel (.csv dengan UTF-8 BOM)
    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->getReportData($request);
        $fileName = 'Laporan_Penjualan_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan UTF-8 BOM agar Excel membaca karakter & angka dengan benar
            fputs($file, "\xEF\xBB\xBF");

            // Header Laporan
            fputcsv($file, ['LAPORAN PENJUALAN & LABA RUGI - MARKASNACK & GLYTCH']);
            fputcsv($file, ['Periode Tanggal', $data['start']->format('d/m/Y') . ' s/d ' . $data['end']->format('d/m/Y')]);
            fputcsv($file, []);

            // Ringkasan Finansial
            fputcsv($file, ['RINGKASAN KEUSNGAN']);
            fputcsv($file, ['Total Omzet Penjualan', $data['grossSales']]);
            fputcsv($file, ['Total Diskon', $data['totalDiscount']]);
            fputcsv($file, ['Omzet Bersih (Net Sales)', $data['netSales']]);
            fputcsv($file, ['Total Modal HPP (FIFO)', $data['cogs']]);
            fputcsv($file, ['Total Beban Operasional', $data['totalExpenses']]);
            fputcsv($file, ['Laba Bersih (Net Profit)', $data['netProfit']]);
            fputcsv($file, []);

            // Tabel Rincian
            fputcsv($file, ['NO. STRUK', 'WAKTU', 'TOKO', 'NAMA PRODUK', 'QTY', 'MODAL HPP (RP)', 'HARGA JUAL (RP)', 'SUBTOTAL (RP)', 'DISKON (RP)', 'LABA KOTOR (RP)']);

            foreach ($data['transactionDetails'] as $detail) {
                $detailCogs = $detail->quantity * $detail->buy_price;
                $detailProfit = ($detail->subtotal - $detail->discount_amount) - $detailCogs;

                fputcsv($file, [
                    $detail->transaction->invoice_number ?? '-',
                    $detail->created_at ? $detail->created_at->format('d/m/Y H:i') : '-',
                    $detail->store->name ?? '-',
                    $detail->product->name ?? '-',
                    $detail->quantity,
                    $detail->buy_price,
                    $detail->sell_price,
                    $detail->subtotal,
                    $detail->discount_amount,
                    $detailProfit
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    // Tampilan Khusus Cetak / Simpan PDF
    public function printPdf(Request $request)
    {
        $data = $this->getReportData($request);
        return view('reports.print', $data);
    }

    // Helper untuk mengambil data sesuai filter
    private function getReportData(Request $request): array
    {
        $period = $request->get('period', 'today');
        $storeId = $request->get('store_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $now = Carbon::now();
        if ($period == 'today') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
        } elseif ($period == 'weekly') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
        } elseif ($period == 'monthly') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($period == 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
        }

        // Query Detail Transaksi (Hanya status completed)
        $detailsQuery = TransactionDetail::with(['store', 'product', 'transaction'])
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$start, $end]);
            });

        if ($storeId) {
            $detailsQuery->where('store_id', $storeId);
        }

        $transactionDetails = $detailsQuery->latest()->get();

        // Query Beban Operasional
        $expenseQuery = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
        if ($storeId) {
            $expenseQuery->where('store_id', $storeId);
        }
        $expenses = $expenseQuery->get();

        // Matematika Laba Rugi
        $grossSales = $transactionDetails->sum('subtotal');
        $totalDiscount = $transactionDetails->sum('discount_amount');
        $netSales = $grossSales - $totalDiscount;

        $cogs = $transactionDetails->sum(function ($item) {
            return $item->quantity * $item->buy_price;
        });

        $grossProfit = $netSales - $cogs;
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;

        $stores = Store::all();

        return compact(
            'transactionDetails',
            'expenses',
            'grossSales',
            'totalDiscount',
            'netSales',
            'cogs',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'stores',
            'period',
            'storeId',
            'startDate',
            'endDate',
            'start',
            'end'
        );
    }
}