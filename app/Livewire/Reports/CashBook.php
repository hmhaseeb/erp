<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CashBook extends Component
{
    use WithPagination;

    public $start_date;
    public $end_date;
    public $search = '';
    public $perPage = 15;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
        $this->search = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function render()
    {
        $cashAccountIds = Account::where('type', 'Cash')->where('status', true)->pluck('id');

        // Opening balance calculation in single query
        $initialOpening = (float) Account::where('type', 'Cash')->where('status', true)->sum('opening_balance');

        $priorStats = DB::table('account_transactions')
            ->whereIn('account_id', $cashAccountIds)
            ->where('transaction_date', '<', $this->start_date)
            ->selectRaw('COALESCE(SUM(debit), 0) as prior_debits, COALESCE(SUM(credit), 0) as prior_credits')
            ->first();

        $openingBalance = $initialOpening + (float)($priorStats->prior_debits ?? 0) - (float)($priorStats->prior_credits ?? 0);

        $query = AccountTransaction::select('id', 'account_id', 'transaction_date', 'transaction_type', 'debit', 'credit', 'balance', 'description')
            ->with('account:id,name')
            ->whereIn('account_id', $cashAccountIds)
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('transaction_type', 'like', '%' . $this->search . '%')
                  ->orWhereHas('account', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $totalDebits = (float) (clone $query)->sum('debit');
        $totalCredits = (float) (clone $query)->sum('credit');
        $closingBalance = $openingBalance + $totalDebits - $totalCredits;

        $transactions = $query->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.reports.cash-book', [
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'closingBalance' => $closingBalance,
        ])->layout('layouts.app', ['title' => 'Cash Book Register']);
    }
}
