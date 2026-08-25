<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\AccountTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $type_filter = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $accountId;
    public $isEditMode = false;
    public $name, $type = 'Cash', $bank_name, $account_number, $opening_balance = 0, $opening_balance_date, $notes;
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
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
        $this->reset(['search', 'type_filter']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:Cash,Bank,Other',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'opening_balance' => 'numeric|min:0',
            'opening_balance_date' => 'required|date',
        ];
    }

    public function mount()
    {
        $this->opening_balance_date = now()->toDateString();
    }

    public function openModal()
    {
        $this->reset(['accountId', 'isEditMode', 'name', 'bank_name', 'account_number', 'opening_balance', 'notes']);
        $this->type = 'Cash';
        $this->opening_balance_date = now()->toDateString();
        $this->isModalOpen = true;
    }

    public function editAccount($id)
    {
        $account = Account::findOrFail($id);
        $this->accountId = $account->id;
        $this->isEditMode = true;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->bank_name = $account->bank_name;
        $this->account_number = $account->account_number;
        $this->opening_balance = $account->opening_balance;
        $this->opening_balance_date = $account->opening_balance_date ?? now()->toDateString();
        $this->notes = $account->notes;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveAccount()
    {
        $this->validate();

        if ($this->isEditMode && $this->accountId) {
            $account = Account::findOrFail($this->accountId);
            $account->update([
                'name' => $this->name,
                'type' => $this->type,
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Account '{$this->name}' updated successfully.");
        } else {
            Account::create([
                'name' => $this->name,
                'type' => $this->type,
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'opening_balance' => $this->opening_balance,
                'opening_balance_date' => $this->opening_balance_date,
                'current_balance' => $this->opening_balance,
                'status' => true,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Account '{$this->name}' created successfully.");
        }

        $this->closeModal();
    }

    public function deleteAccount($id)
    {
        $account = Account::findOrFail($id);
        if ($account->current_balance != 0) {
            session()->flash('error', "Cannot delete account with a non-zero balance (AED {$account->current_balance}).");
            return;
        }

        $hasTransactions = AccountTransaction::where('account_id', $account->id)->exists();
        if ($hasTransactions) {
            session()->flash('error', "Cannot delete account with existing transaction history.");
            return;
        }

        $account->delete();
        session()->flash('success', 'Account deleted successfully.');
    }

    public function render()
    {
        $totalCash = Account::where('type', 'Cash')->sum('current_balance');
        $totalBank = Account::where('type', 'Bank')->sum('current_balance');
        $totalLiquid = $totalCash + $totalBank;

        $query = Account::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('bank_name', 'like', '%' . $this->search . '%')
                  ->orWhere('account_number', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }

        $accounts = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.accounts.index', [
            'accounts' => $accounts,
            'totalCash' => $totalCash,
            'totalBank' => $totalBank,
            'totalLiquid' => $totalLiquid,
        ])->layout('layouts.app', ['title' => 'Cash & Bank Accounts']);
    }
}
