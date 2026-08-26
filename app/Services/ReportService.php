<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get Daily Summary metrics for a specific date (or today) via direct database aggregation.
     */
    public function getDailyReport(string $date): array
    {
        // 1. Sales metrics in single aggregate query
        $salesAgg = DB::table('sales')
            ->where('sale_date', $date)
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_count,
                COALESCE(SUM(grand_total), 0) as gross_sales,
                COALESCE(SUM(CASE WHEN payment_type = "Cash" THEN grand_total ELSE 0 END), 0) as cash_sales,
                COALESCE(SUM(CASE WHEN payment_type = "Bank" THEN grand_total ELSE 0 END), 0) as bank_sales,
                COALESCE(SUM(CASE WHEN payment_type = "Credit" THEN grand_total ELSE 0 END), 0) as credit_sales,
                COALESCE(SUM(vat_amount), 0) as sales_vat,
                COALESCE(SUM(discount_amount), 0) as sales_discounts
            ')->first();

        $salesReturns = (float) DB::table('sales_returns')
            ->where('return_date', $date)
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->sum('grand_total');

        $grossSales = (float) ($salesAgg->gross_sales ?? 0);
        $netSales = $grossSales - $salesReturns;

        // 2. Purchase metrics in single aggregate query
        $purchasesAgg = DB::table('purchases')
            ->where('purchase_date', $date)
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_count,
                COALESCE(SUM(grand_total), 0) as gross_purchases,
                COALESCE(SUM(CASE WHEN payment_type = "Cash" THEN grand_total ELSE 0 END), 0) as cash_purchases,
                COALESCE(SUM(CASE WHEN payment_type = "Bank" THEN grand_total ELSE 0 END), 0) as bank_purchases,
                COALESCE(SUM(CASE WHEN payment_type = "Credit" THEN grand_total ELSE 0 END), 0) as credit_purchases
            ')->first();

        $purchaseReturns = (float) DB::table('purchase_returns')
            ->where('return_date', $date)
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->sum('grand_total');

        $grossPurchases = (float) ($purchasesAgg->gross_purchases ?? 0);
        $netPurchases = $grossPurchases - $purchaseReturns;

        // 3. Cash & Bank balances
        $cashBalance = (float) DB::table('accounts')->where('type', 'Cash')->where('status', 1)->whereNull('deleted_at')->sum('current_balance');
        $bankBalance = (float) DB::table('accounts')->where('type', 'Bank')->where('status', 1)->whereNull('deleted_at')->sum('current_balance');

        $incomeTotal = (float) DB::table('incomes')->where('date', $date)->whereNull('deleted_at')->sum('amount');
        $expenseTotal = (float) DB::table('expenses')->where('date', $date)->whereNull('deleted_at')->sum('amount');

        return [
            'date' => $date,
            'sales' => [
                'count' => (int) ($salesAgg->total_count ?? 0),
                'gross' => $grossSales,
                'cash' => (float) ($salesAgg->cash_sales ?? 0),
                'bank' => (float) ($salesAgg->bank_sales ?? 0),
                'credit' => (float) ($salesAgg->credit_sales ?? 0),
                'vat' => (float) ($salesAgg->sales_vat ?? 0),
                'discounts' => (float) ($salesAgg->sales_discounts ?? 0),
                'returns' => $salesReturns,
                'net' => $netSales,
            ],
            'purchases' => [
                'count' => (int) ($purchasesAgg->total_count ?? 0),
                'gross' => $grossPurchases,
                'cash' => (float) ($purchasesAgg->cash_purchases ?? 0),
                'bank' => (float) ($purchasesAgg->bank_purchases ?? 0),
                'credit' => (float) ($purchasesAgg->credit_purchases ?? 0),
                'returns' => $purchaseReturns,
                'net' => $netPurchases,
            ],
            'income' => $incomeTotal,
            'expense' => $expenseTotal,
            'cash_balance' => $cashBalance,
            'bank_balance' => $bankBalance,
        ];
    }

    /**
     * Profit and Loss statement calculation for a date range with detailed breakdowns.
     */
    public function getProfitLossReport(string $startDate, string $endDate): array
    {
        // 1. Operating Revenue
        $salesAgg = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as gross_sales,
                COALESCE(SUM(discount_amount), 0) as discounts,
                COALESCE(SUM(vat_amount), 0) as vat_amount,
                COALESCE(SUM(subtotal), 0) as subtotal,
                COALESCE(SUM(cogs_total), 0) as cogs_total
            ')->first();

        $salesReturnsAgg = DB::table('sales_returns')
            ->whereBetween('return_date', [$startDate, $endDate])
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as returns_grand_total,
                COALESCE(SUM(subtotal), 0) as returns_subtotal,
                COALESCE(SUM(cogs_total), 0) as returns_cogs
            ')->first();

        $grossSales = (float) ($salesAgg->gross_sales ?? 0);
        $salesReturns = (float) ($salesReturnsAgg->returns_grand_total ?? 0);
        $netSales = max(0, $grossSales - $salesReturns);

        // 2. Cost of Goods Sold / Purchases
        $purchasesAgg = DB::table('purchases')
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total_purchases')->first();

        $purchaseReturnsAgg = DB::table('purchase_returns')
            ->whereBetween('return_date', [$startDate, $endDate])
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total_returns')->first();

        $totalPurchases = (float) ($purchasesAgg->total_purchases ?? 0);
        $totalPurchaseReturns = (float) ($purchaseReturnsAgg->total_returns ?? 0);
        $netPurchases = max(0, $totalPurchases - $totalPurchaseReturns);

        // COGS from weighted inventory cost
        $salesCogs = (float) ($salesAgg->cogs_total ?? 0);
        $returnsCogs = (float) ($salesReturnsAgg->returns_cogs ?? 0);
        $cogs = max(0, $salesCogs - $returnsCogs);

        // Gross profit based on Net Sales and COGS / Net Purchases
        $grossProfit = $netPurchases > 0 ? ($netSales - $netPurchases) : ($netSales - $cogs);

        // 3. Other Operating Income & Breakdown
        $incomeBreakdown = DB::table('incomes')
            ->leftJoin('income_categories', 'incomes.income_category_id', '=', 'income_categories.id')
            ->whereBetween('incomes.date', [$startDate, $endDate])
            ->whereNull('incomes.deleted_at')
            ->selectRaw('COALESCE(income_categories.name, "General Income") as cat_name, SUM(incomes.amount) as total_amt')
            ->groupBy('cat_name')
            ->pluck('total_amt', 'cat_name')
            ->toArray();

        $otherIncome = (float) array_sum($incomeBreakdown);

        // 4. Operating Expenses & Breakdown
        $expensesBreakdown = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->whereNull('expenses.deleted_at')
            ->selectRaw('COALESCE(expense_categories.name, "General Expense") as cat_name, SUM(expenses.amount) as total_amt')
            ->groupBy('cat_name')
            ->pluck('total_amt', 'cat_name')
            ->toArray();

        $operatingExpenses = (float) array_sum($expensesBreakdown);

        $netProfit = $grossProfit + $otherIncome - $operatingExpenses;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => [
                'gross_sales' => $grossSales,
                'sales_returns' => $salesReturns,
                'net_sales' => $netSales,
            ],
            'cogs' => [
                'purchases' => $totalPurchases,
                'purchase_returns' => $totalPurchaseReturns,
                'net_purchases' => $netPurchases,
                'inventory_cogs' => $cogs,
            ],
            'gross_profit' => $grossProfit,
            'other_income' => $otherIncome,
            'other_income_breakdown' => $incomeBreakdown,
            'expenses' => $operatingExpenses,
            'expenses_breakdown' => $expensesBreakdown,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Alias for backward compatibility.
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
        return $this->getProfitLossReport($startDate, $endDate);
    }

    /**
     * Stock Valuation Summary
     */
    public function getStockValuation(): array
    {
        $stockAgg = DB::table('products')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_products,
                COALESCE(SUM(current_stock), 0) as total_quantity,
                COALESCE(SUM(current_stock * weighted_cost), 0) as total_valuation
            ')->first();

        return [
            'total_products' => (int) ($stockAgg->total_products ?? 0),
            'total_quantity' => (float) ($stockAgg->total_quantity ?? 0),
            'total_valuation' => (float) ($stockAgg->total_valuation ?? 0),
        ];
    }
}
