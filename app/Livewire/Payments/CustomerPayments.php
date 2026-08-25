<?php

namespace App\Livewire\Payments;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Services\PaymentService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerPayments extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $customer_id_filter = '';
    public $account_id_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $customer_id, $account_id, $amount = 0, $payment_date, $payment_number, $reference_number, $notes;
    public $allocations = [];
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCustomerIdFilter()
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
        $this->reset(['search', 'customer_id_filter', 'account_id_filter', 'date_from', 'date_to']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function mount()
    {
        $this->payment_date = now()->toDateString();
        $firstCust = Customer::first();
        if ($firstCust) {
            $this->customer_id = $firstCust->id;
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
            'customer_id' => 'required|exists:customers,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|gt:0',
            'payment_date' => 'required|date',
        ]);

        try {
            $paymentService->recordCustomerPayment(
                $this->customer_id,
                $this->account_id,
                (float) $this->amount,
                $this->payment_date,
                null,
                $this->reference_number,
                $this->notes,
                $this->allocations
            );

            session()->flash('success', 'Customer receipt payment voucher recorded successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();
        $unpaidSales = [];

        if ($this->customer_id) {
            $unpaidSales = Sale::where('customer_id', $this->customer_id)
                ->where('due_amount', '>', 0)
                ->where('status', 'Confirmed')
                ->get();
        }

        $query = CustomerPayment::with(['customer', 'account']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('payment_number', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('account', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->customer_id_filter) {
            $query->where('customer_id', $this->customer_id_filter);
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

        return view('livewire.payments.customer-payments', [
            'customers' => $customers,
            'accounts' => $accounts,
            'unpaidSales' => $unpaidSales,
            'payments' => $payments,
        ])->layout('layouts.app', ['title' => 'Customer Payments & Receipts']);
    }
}
