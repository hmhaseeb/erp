<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Purchase;
use App\Models\Sale;
use App\Services\ReportService;
use Livewire\Component;

class DailyReport extends Component
{
    public $date;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function render(ReportService $reportService)
    {
        $reportData = $reportService->getDailyReport($this->date);

        $sales = Sale::with('customer')
            ->whereDate('sale_date', $this->date)
            ->where('status', 'Confirmed')
            ->get();

        $purchases = Purchase::with('supplier')
            ->whereDate('purchase_date', $this->date)
            ->where('status', 'Confirmed')
            ->get();

        $incomes = Income::with(['category', 'account'])
            ->whereDate('date', $this->date)
            ->get();

        $expenses = Expense::with(['category', 'account'])
            ->whereDate('date', $this->date)
            ->get();

        return view('livewire.reports.daily-report', [
            'report' => $reportData,
            'sales' => $sales,
            'purchases' => $purchases,
            'incomes' => $incomes,
            'expenses' => $expenses,
        ])->layout('layouts.app', ['title' => 'Daily Business Report']);
    }
}
