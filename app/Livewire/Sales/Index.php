<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Sale;
use App\Services\SalesService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $customer_id_filter = '';
    public $payment_type_filter = '';
    public $status_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $selectedSale = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCustomerIdFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
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
        $this->reset(['search', 'customer_id_filter', 'payment_type_filter', 'status_filter', 'date_from', 'date_to']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function viewDetails($id)
    {
        $this->selectedSale = Sale::with(['customer', 'account', 'items.product'])->find($id);
    }

    public function closeDetails()
    {
        $this->selectedSale = null;
    }

    public function cancelSale($id, SalesService $salesService)
    {
        try {
            $salesService->cancelSale($id);
            session()->flash('success', 'Sales invoice cancelled, stock restored, and transactions reversed successfully.');
            $this->closeDetails();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Sale::select('id', 'invoice_number', 'sale_date', 'customer_id', 'account_id', 'payment_type', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with(['customer:id,name,company_name', 'account:id,name']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('payment_type', 'like', '%' . $this->search . '%')
                  ->orWhere('grand_total', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->customer_id_filter) {
            $query->where('customer_id', $this->customer_id_filter);
        }

        if ($this->payment_type_filter) {
            $query->where('payment_type', $this->payment_type_filter);
        }

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        if ($this->date_from) {
            $query->whereDate('sale_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('sale_date', '<=', $this->date_to);
        }

        $sales = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $customers = Customer::select('id', 'name', 'company_name')->orderBy('name')->get();

        return view('livewire.sales.index', [
            'sales' => $sales,
            'customers' => $customers,
        ])->layout('layouts.app', ['title' => 'Sales Invoices']);
    }
}
