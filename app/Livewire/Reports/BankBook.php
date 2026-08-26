<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BankBook extends Component
{
    use WithPagination;

    public $account_id;
    public $start_date;
    public $end_date;
    public $search = '';
    public $perPage = 15;

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();

        $firstBank = Account::where('type', 'Bank')->where('status', true)->first();
        if ($firstBank) {
            $this->account_id = $firstBank->id;
        } else {
            $any = Account::where('status', true)->first();
            if ($any) {
                $this->account_id = $any->id;
            }
        }
    }

    public function updatedAccountId()
    {
        $this->resetPage();
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
        $bankAccounts = Account::select('id', 'name', 'bank_name', 'account_number', 'current_balance')
            ->where('type', 'Bank')
            ->where('status', true)
            ->get();

        $selectedAccount = Account::find($this->account_id);

        $openingBalance = 0;
        $totalDebits = 0;
        $totalCredits = 0;
        $closingBalance = 0;
        $transactions = null;

        if ($selectedAccount) {
            $priorStats = DB::table('account_transactions')
                ->where('account_id', $selectedAccount->id)
                ->where('transaction_date', '<', $this->start_date)
                ->selectRaw('COALESCE(SUM(debit), 0) as prior_debits, COALESCE(SUM(credit), 0) as prior_credits')
                ->first();

            $openingBalance = (float)$selectedAccount->opening_balance + (float)($priorStats->prior_debits ?? 0) - (float)($priorStats->prior_credits ?? 0);

            $query = AccountTransaction::select('id', 'account_id', 'transaction_date', 'transaction_type', 'debit', 'credit', 'balance', 'description')
                ->where('account_id', $selectedAccount->id)
                ->whereBetween('transaction_date', [$this->start_date, $this->end_date]);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_type', 'like', '%' . $this->search . '%');
                });
            }

            $totalDebits = (float) (clone $query)->sum('debit');
            $totalCredits = (float) (clone $query)->sum('credit');
            $closingBalance = $openingBalance + $totalDebits - $totalCredits;

            $transactions = $query->orderBy('transaction_date', 'asc')
                ->orderBy('id', 'asc')
                ->paginate($this->perPage);
        }

        return view('livewire.reports.bank-book', [
            'bankAccounts' => $bankAccounts,
            'selectedAccount' => $selectedAccount,
            'openingBalance' => $openingBalance,
            'transactions' => $transactions,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'closingBalance' => $closingBalance,
        ])->layout('layouts.app', ['title' => 'Bank Book Statement']);
    }
}
