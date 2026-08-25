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
     * Profit and Loss statement calculation for a date range.
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
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

        $returnsAgg = DB::table('sales_returns')
            ->whereBetween('return_date', [$startDate, $endDate])
            ->where('status', 'Confirmed')
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(subtotal), 0) as returns_subtotal,
                COALESCE(SUM(cogs_total), 0) as returns_cogs
            ')->first();

        $salesSubtotal = (float) ($salesAgg->subtotal ?? 0);
        $salesReturnsSubtotal = (float) ($returnsAgg->returns_subtotal ?? 0);
        $netSalesRevenue = max(0, $salesSubtotal - $salesReturnsSubtotal);

        // COGS calculation
        $salesCogs = (float) ($salesAgg->cogs_total ?? 0);
        $returnsCogs = (float) ($returnsAgg->returns_cogs ?? 0);
        $cogs = max(0, $salesCogs - $returnsCogs);

        $grossProfit = $netSalesRevenue - $cogs;

        // Other Income & Expenses
        $otherIncome = (float) DB::table('incomes')->whereBetween('date', [$startDate, $endDate])->whereNull('deleted_at')->sum('amount');
        $operatingExpenses = (float) DB::table('expenses')->whereBetween('date', [$startDate, $endDate])->whereNull('deleted_at')->sum('amount');

        $netProfit = $grossProfit + $otherIncome - $operatingExpenses;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'gross_sales' => (float) ($salesAgg->gross_sales ?? 0),
            'sales_subtotal' => $salesSubtotal,
            'sales_returns' => $salesReturnsSubtotal,
            'net_sales_revenue' => $netSalesRevenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'other_income' => $otherIncome,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $netProfit,
        ];
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
