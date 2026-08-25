<?php

namespace App\Livewire\Payments;

use App\Models\Account;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierPayments extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $supplier_id_filter = '';
    public $account_id_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $supplier_id, $account_id, $amount = 0, $payment_date, $payment_number, $reference_number, $notes;
    public $allocations = [];
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSupplierIdFilter()
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
        $this->reset(['search', 'supplier_id_filter', 'account_id_filter', 'date_from', 'date_to']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function mount()
    {
        $this->payment_date = now()->toDateString();
        $firstSup = Supplier::first();
        if ($firstSup) {
            $this->supplier_id = $firstSup->id;
        }
        $firstAcc = Account::first();
        if ($firstAcc) {
            $this->account_id = $firstAcc->id;
        }
    }

    public function openModal()
    {
        $this->reset(['amount', 'reference_number', 'notes', 'allocations']);
        $this->payment_date = now()->toDateString();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function savePayment(PaymentService $paymentService)
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|gt:0',
            'payment_date' => 'required|date',
        ]);

        try {
            $paymentService->recordSupplierPayment(
                $this->supplier_id,
                $this->account_id,
                (float) $this->amount,
                $this->payment_date,
                null,
                $this->reference_number,
                $this->notes,
                $this->allocations
            );

            session()->flash('success', 'Supplier payment voucher recorded successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $unpaidPurchases = [];

        if ($this->supplier_id) {
            $unpaidPurchases = Purchase::where('supplier_id', $this->supplier_id)
                ->where('due_amount', '>', 0)
                ->where('status', 'Confirmed')
                ->get();
        }

        $query = SupplierPayment::with(['supplier', 'account']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('payment_number', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function ($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('account', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->supplier_id_filter) {
            $query->where('supplier_id', $this->supplier_id_filter);
        }

        if ($this->account_id_filter) {
            $query->where('account_id', $this->account_id_filter);
        }

        if ($this->date_from) {
            $query->whereDate('payment_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('payment_date', '<=', $this->date_to);
        }

        $payments = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.payments.supplier-payments', [
            'suppliers' => $suppliers,
            'accounts' => $accounts,
            'unpaidPurchases' => $unpaidPurchases,
            'payments' => $payments,
        ])->layout('layouts.app', ['title' => 'Supplier Payments']);
    }
}
