<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\AccountTransaction;
use Livewire\Component;

class Ledger extends Component
{
    public $account_id;
    public $start_date;
    public $end_date;
    public $search = '';

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
    }

    public function render()
    {
        $accounts = Account::orderBy('name')->get();
        $selectedAccount = Account::find($this->account_id);

        $openingBalance = 0;
        $transactions = collect();

        if ($selectedAccount) {
            // Calculate historical balance before start_date
            $priorDebits = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereDate('transaction_date', '<', $this->start_date)
                ->sum('debit');
            $priorCredits = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereDate('transaction_date', '<', $this->start_date)
                ->sum('credit');

            $openingBalance = $selectedAccount->opening_balance + $priorDebits - $priorCredits;

            $query = AccountTransaction::where('account_id', $selectedAccount->id)
                ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_type', 'like', '%' . $this->search . '%')
                      ->orWhere('reference_type', 'like', '%' . $this->search . '%');
                });
            }

            $transactions = $query->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        }

        $totalDebits = $transactions->sum('debit');
        $totalCredits = $transactions->sum('credit');
        $closingBalance = $openingBalance + $totalDebits - $totalCredits;

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
