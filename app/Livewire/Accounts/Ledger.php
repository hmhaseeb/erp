<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\AccountTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class Ledger extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_id;
    public $start_date;
    public $end_date;
    public $search = '';
    public $perPage = 15;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAccountId()
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

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();

        $accountParam = request()->query('account_id');
        if ($accountParam) {
            $this->account_id = $accountParam;
        } else {
            $first = Account::first();
            if ($first) {
                $this->account_id = $first->id;
            }
        }
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
        $accounts = Account::orderBy('name')->get();
        $selectedAccount = Account::find($this->account_id);

        $openingBalance = 0;
        $totalDebits = 0;
        $totalCredits = 0;
        $closingBalance = 0;
        $transactions = collect();

        if ($selectedAccount) {
            // Calculate historical balance before start_date
            $priorDebits = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereDate('transaction_date', '<', $this->start_date)
                ->sum('debit');
            $priorCredits = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereDate('transaction_date', '<', $this->start_date)
                ->sum('credit');

            $openingBalance = (float)$selectedAccount->opening_balance + (float)$priorDebits - (float)$priorCredits;

            $query = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_type', 'like', '%' . $this->search . '%')
                      ->orWhere('reference_type', 'like', '%' . $this->search . '%');
                });
            }

            $metricsQuery = clone $query;
            $totalDebits = (float) $metricsQuery->sum('debit');
            $totalCredits = (float) $metricsQuery->sum('credit');
            $closingBalance = $openingBalance + $totalDebits - $totalCredits;

            $transactions = $query->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->paginate((int)$this->perPage);
        }

        return view('livewire.accounts.ledger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'openingBalance' => $openingBalance,
            'transactions' => $transactions,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'closingBalance' => $closingBalance,
        ])->layout('layouts.app', ['title' => 'Account Ledger Statement']);
    }
}
