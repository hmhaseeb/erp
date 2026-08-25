<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Services\AccountingService;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $account_id_filter = '';
    public $type_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 15;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $account_id, $to_account_id, $type = 'Cash In', $amount = 0, $date, $description;
    public $isModalOpen = false;

    protected $rules = [
        'account_id' => 'required|exists:accounts,id',
        'type' => 'required|in:Cash In,Cash Out,Bank Deposit,Bank Withdrawal,Transfer',
        'amount' => 'required|numeric|gt:0',
        'date' => 'required|date',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAccountIdFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'account_id_filter', 'type_filter', 'date_from', 'date_to']);
        $this->perPage = 15;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function mount()
    {
        $this->date = now()->toDateString();
        $firstAcc = Account::first();
        if ($firstAcc) {
            $this->account_id = $firstAcc->id;
        }
    }

    public function openModal()
    {
        $this->reset(['amount', 'description', 'to_account_id']);
        $this->date = now()->toDateString();
        $firstAcc = Account::first();
        if ($firstAcc) {
            $this->account_id = $firstAcc->id;
        }
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveTransaction(AccountingService $accountingService)
    {
        $this->validate();

        if ($this->type === 'Transfer') {
            if (!$this->to_account_id) {
                $this->addError('to_account_id', 'Destination account is required for transfers.');
                return;
            }
            if ($this->account_id == $this->to_account_id) {
                $this->addError('to_account_id', 'Destination account must be different from source account.');
                return;
            }

            $accountingService->transfer(
                $this->account_id,
                $this->to_account_id,
                (float) $this->amount,
                $this->date,
                $this->description
            );
        } else {
            $isDebit = in_array($this->type, ['Cash In', 'Bank Deposit']);
            $debit = $isDebit ? (float) $this->amount : 0;
            $credit = !$isDebit ? (float) $this->amount : 0;

            $accountingService->recordTransaction(
                $this->account_id,
                $this->date,
                $this->type,
                $debit,
                $credit,
                'Manual',
                null,
                $this->description
            );
        }

        session()->flash('success', 'Account transaction recorded successfully.');
        $this->closeModal();
    }

    public function render()
    {
        $accounts = Account::orderBy('name')->get();
        $query = AccountTransaction::with('account');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('transaction_type', 'like', '%' . $this->search . '%')
                  ->orWhere('debit', 'like', '%' . $this->search . '%')
                  ->orWhere('credit', 'like', '%' . $this->search . '%')
                  ->orWhereHas('account', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->account_id_filter) {
            $query->where('account_id', $this->account_id_filter);
        }

        if ($this->type_filter) {
            $query->where('transaction_type', $this->type_filter);
        }

        if ($this->date_from) {
            $query->whereDate('transaction_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('transaction_date', '<=', $this->date_to);
        }

        $transactions = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.accounts.transactions', [
            'accounts' => $accounts,
            'transactions' => $transactions,
        ])->layout('layouts.app', ['title' => 'Account Transactions']);
    }
}
