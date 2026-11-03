<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Expense;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. Stat Cards (Hanya menghitung transaksi completed)
        $todaySales = Transaction::where('status', 'completed')->whereDate('created_at', $today)->sum('total_amount');
        $todayTransactionsCount = Transaction::where('status', 'completed')->whereDate('created_at', $today)->count();
        
        $monthlySales = Transaction::where('status', 'completed')->where('created_at', '>=', $thisMonth)->sum('total_amount');
        $monthlyExpenses = Expense::where('expense_date', '>=', $thisMonth->toDateString())->sum('amount');
        
        $monthlyCogs = TransactionDetail::whereHas('transaction', function ($q) use ($thisMonth) {
            $q->where('status', 'completed')->where('created_at', '>=', $thisMonth);
        })->get()->sum(function ($item) {
            return $item->quantity * $item->buy_price;
        });

        $monthlyNetProfit = ($monthlySales - $monthlyCogs) - $monthlyExpenses;

        // 2. Alert Stok Kritis (Batch dengan stok <= 5)
        $lowStockBatches = ProductBatch::with(['product.store'])
            ->where('current_stock', '<=', 5)
            ->where('current_stock', '>', 0)
            ->take(5)
            ->get();

        // 3. Top 5 Produk Terlaris Bulan Ini (Hanya transaksi completed)
        $topProducts = TransactionDetail::select(
                'product_id', 
                DB::raw('SUM(quantity) as total_qty'), 
                DB::raw('SUM(subtotal - discount_amount) as total_sales')
            )
            ->whereHas('transaction', function ($q) use ($thisMonth) {
                $q->where('status', 'completed')->where('created_at', '>=', $thisMonth);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with(['product.store'])
            ->take(5)
            ->get();

        // 4. Data Grafik Penjualan 7 Hari Terakhir (Hanya transaksi completed)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = Transaction::where('status', 'completed')->whereDate('created_at', $date)->sum('total_amount');
        }

        $totalStores = Store::count();

        return view('dashboard', compact(
            'todaySales',
            'todayTransactionsCount',
            'monthlySales',
            'monthlyNetProfit',
            'lowStockBatches',
            'topProducts',
            'chartLabels',
            'chartData',
            'totalStores'
        ));
    }
}