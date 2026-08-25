<?php

namespace App\Livewire\Expenses;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $category_id_filter = '';
    public $account_id_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 12;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $expense_category_id, $account_id, $amount = 0, $date, $description, $reference_number, $attachment, $notes;
    public $isModalOpen = false;

    protected $rules = [
        'expense_category_id' => 'required|exists:expense_categories,id',
        'account_id' => 'required|exists:accounts,id',
        'amount' => 'required|numeric|gt:0',
        'date' => 'required|date',
        'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:2048',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryIdFilter()
    {
        $this->resetPage();
    }

    public function updatedAccountIdFilter()
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
        $this->reset(['search', 'category_id_filter', 'account_id_filter', 'date_from', 'date_to']);
        $this->perPage = 12;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function mount()
    {
        $this->date = now()->toDateString();
        $firstCat = ExpenseCategory::first();
        if ($firstCat) {
            $this->expense_category_id = $firstCat->id;
        }
        $firstAcc = Account::first();
        if ($firstAcc) {
            $this->account_id = $firstAcc->id;
        }
    }

    public function openModal()
    {
        $this->reset(['amount', 'description', 'reference_number', 'attachment', 'notes']);
        $this->date = now()->toDateString();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveExpense(AccountingService $accountingService)
    {
        $this->validate();

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('expense_attachments', 'public');
        }

        $expense = Expense::create([
            'date' => $this->date,
            'expense_category_id' => $this->expense_category_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'reference_number' => $this->reference_number,
            'attachment' => $path,
            'notes' => $this->notes,
        ]);

        // Account Credit (Outflow)
        $category = ExpenseCategory::find($this->expense_category_id);
        $accountingService->recordTransaction(
            $this->account_id,
            $this->date,
            'Expense',
            0,
            (float) $this->amount,
            Expense::class,
            $expense->id,
            "Expense: {$category->name} - " . ($this->description ?? '')
        );

        session()->flash('success', 'Expense entry saved successfully.');
        $this->closeModal();
    }

    public function render()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $query = Expense::with(['category', 'account']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('amount', 'like', '%' . $this->search . '%')
                  ->orWhereHas('category', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('account', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->category_id_filter) {
            $query->where('expense_category_id', $this->category_id_filter);
        }

        if ($this->account_id_filter) {
            $query->where('account_id', $this->account_id_filter);
        }

        if ($this->date_from) {
            $query->whereDate('date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('date', '<=', $this->date_to);
        }

        $expenses = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.expenses.index', [
            'categories' => $categories,
            'accounts' => $accounts,
            'expenses' => $expenses,
        ])->layout('layouts.app', ['title' => 'Operating Expenses']);
    }
}
