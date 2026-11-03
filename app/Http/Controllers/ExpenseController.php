<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Store;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('store')->latest('expense_date')->get();
        $stores = Store::all();
        $totalExpense = $expenses->sum('amount');

        return view('expenses.index', compact('expenses', 'stores', 'totalExpense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id'     => 'required|exists:stores,id',
            'title'        => 'required|string|max:255',
            'category'     => 'required|in:tenda,transportasi,operasional,konsumsi,lainnya',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note'         => 'nullable|string',
        ]);

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran operasional berhasil dicatat!');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'store_id'     => 'required|exists:stores,id',
            'title'        => 'required|string|max:255',
            'category'     => 'required|in:tenda,transportasi,operasional,konsumsi,lainnya',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note'         => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Catatan pengeluaran berhasil diperbarui!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Catatan pengeluaran berhasil dihapus!');
    }
}