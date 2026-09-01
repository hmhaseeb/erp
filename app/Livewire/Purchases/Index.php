<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $supplier_id_filter = '';
    public $payment_type_filter = '';
    public $status_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $selectedPurchase = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSupplierIdFilter()
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
        $this->reset(['search', 'supplier_id_filter', 'payment_type_filter', 'status_filter', 'date_from', 'date_to']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function viewDetails($id)
    {
        $this->selectedPurchase = Purchase::with(['supplier', 'account', 'items.product'])->find($id);
    }

    public function closeDetails()
    {
        $this->selectedPurchase = null;
    }

    public function cancelPurchase($id, PurchaseService $purchaseService)
    {
        try {
            $purchaseService->cancelPurchase($id);
            session()->flash('success', 'Purchase invoice cancelled, stock deducted, and financial transactions reversed successfully.');
            $this->closeDetails();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Purchase::select('id', 'purchase_number', 'reference_number', 'purchase_date', 'supplier_id', 'account_id', 'payment_type', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with(['supplier:id,name,company_name', 'account:id,name']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('purchase_number', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('payment_type', 'like', '%' . $this->search . '%')
                  ->orWhere('grand_total', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function ($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->supplier_id_filter) {
            $query->where('supplier_id', $this->supplier_id_filter);
        }

        if ($this->payment_type_filter) {
            $query->where('payment_type', $this->payment_type_filter);
        }

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        if ($this->date_from) {
            $query->whereDate('purchase_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('purchase_date', '<=', $this->date_to);
        }

        $purchases = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $suppliers = Supplier::select('id', 'name', 'company_name')->orderBy('name')->get();

        return view('livewire.purchases.index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
        ])->layout('layouts.app', ['title' => 'Purchase Invoices']);
    }
}
