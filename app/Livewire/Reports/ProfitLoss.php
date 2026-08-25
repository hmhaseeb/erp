<?php

namespace App\Livewire\Reports;

use App\Services\ReportService;
use Livewire\Component;

class ProfitLoss extends Component
{
    public $start_date;
    public $end_date;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
    }

    public function setPeriod($period)
    {
        if ($period === 'today') {
            $this->start_date = now()->toDateString();
            $this->end_date = now()->toDateString();
        } elseif ($period === 'this_month') {
            $this->start_date = now()->startOfMonth()->toDateString();
            $this->end_date = now()->toDateString();
        } elseif ($period === 'last_month') {
            $this->start_date = now()->subMonth()->startOfMonth()->toDateString();
            $this->end_date = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($period === 'this_year') {
            $this->start_date = now()->startOfYear()->toDateString();
            $this->end_date = now()->toDateString();
        }
    }

    public function render(ReportService $reportService)
    {
        $data = $reportService->getProfitLossReport($this->start_date, $this->end_date);

        return view('livewire.reports.profit-loss', [
            'report' => $data,
        ])->layout('layouts.app', ['title' => 'Profit & Loss Statement']);
    }
}
