<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $today = now()->toDateString();

        // 1. Single database aggregate metrics for today
        $todaySales = (float) Sale::where('sale_date', $today)->where('status', 'Confirmed')->sum('grand_total');
        $todayPurchases = (float) Purchase::where('purchase_date', $today)->where('status', 'Confirmed')->sum('grand_total');
        $todayIncome = (float) Income::where('date', $today)->sum('amount');
        $todayExpense = (float) Expense::where('date', $today)->sum('amount');

        // 2. Liquid account balances
        $cashBalance = (float) Account::where('type', 'Cash')->where('status', true)->sum('current_balance');
        $bankBalance = (float) Account::where('type', 'Bank')->where('status', true)->sum('current_balance');

        // 3. Receivables & Payables
        $receivables = (float) Customer::where('status', true)->sum('current_balance');
        $payables = (float) Supplier::where('status', true)->sum('current_balance');

        // 4. Fast MySQL-computed Stock Asset Valuation
        $stockValue = (float) Product::where('status', true)
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(current_stock * weighted_cost), 0) as total_val')
            ->value('total_val') ?? 0;

        // 5. Low stock alerts (only needed columns)
        $lowStockProducts = Product::select('id', 'name', 'product_code', 'current_stock', 'min_stock', 'image', 'unit_id')
            ->with('unit:id,name')
            ->where('status', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->take(8)
            ->get();

        // 6. Recent Sales & Purchases (selective columns & eager relationships)
        $recentSales = Sale::select('id', 'invoice_number', 'sale_date', 'customer_id', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with('customer:id,name,company_name')
            ->where('status', 'Confirmed')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::select('id', 'purchase_number', 'purchase_date', 'supplier_id', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with('supplier:id,name,company_name')
            ->where('status', 'Confirmed')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'todaySales' => $todaySales,
            'todayPurchases' => $todayPurchases,
            'todayIncome' => $todayIncome,
            'todayExpense' => $todayExpense,
            'cashBalance' => $cashBalance,
            'bankBalance' => $bankBalance,
            'receivables' => $receivables,
            'payables' => $payables,
            'stockValue' => $stockValue,
            'lowStockProducts' => $lowStockProducts,
            'recentSales' => $recentSales,
            'recentPurchases' => $recentPurchases,
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
