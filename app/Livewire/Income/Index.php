<?php

namespace App\Livewire\Income;

use App\Models\Account;
use App\Models\Income;
use App\Models\IncomeCategory;
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

    public $income_category_id, $account_id, $amount = 0, $date, $description, $reference_number, $attachment, $notes;
    public $isModalOpen = false;

    protected $rules = [
        'income_category_id' => 'required|exists:income_categories,id',
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
        $firstCat = IncomeCategory::first();
        if ($firstCat) {
            $this->income_category_id = $firstCat->id;
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

    public function saveIncome(AccountingService $accountingService)
    {
        $this->validate();

        $path = null;
        if ($this->attachment) {
            $path = $this->attachment->store('income_attachments', 'public');
        }

        $income = Income::create([
            'date' => $this->date,
            'income_category_id' => $this->income_category_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'reference_number' => $this->reference_number,
            'attachment' => $path,
            'notes' => $this->notes,
        ]);

        // Account Debit (Inflow)
        $category = IncomeCategory::find($this->income_category_id);
        $accountingService->recordTransaction(
            $this->account_id,
            $this->date,
            'Income',
            (float) $this->amount,
            0,
            Income::class,
            $income->id,
            "Income: {$category->name} - " . ($this->description ?? '')
        );

        session()->flash('success', 'Income entry saved successfully.');
        $this->closeModal();
    }

    public function render()
    {
        $categories = IncomeCategory::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $query = Income::with(['category', 'account']);

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
            $query->where('income_category_id', $this->category_id_filter);
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

        $incomes = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.income.index', [
            'categories' => $categories,
            'accounts' => $accounts,
            'incomes' => $incomes,
        ])->layout('layouts.app', ['title' => 'Income Transactions']);
    }
}
