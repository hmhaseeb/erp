<?php

namespace App\Livewire\Reports;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PayablesReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filter_balance = 'with_balance';
    public $perPage = 15;
    public $sortField = 'current_balance';
    public $sortDirection = 'desc';

    public $selectedSupplier = null;
    public $supplierBills = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterBalance()
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
        $this->reset(['search']);
        $this->filter_balance = 'with_balance';
        $this->perPage = 15;
        $this->sortField = 'current_balance';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function viewSupplierBills($supplierId)
    {
        $this->selectedSupplier = Supplier::select('id', 'name', 'company_name', 'mobile', 'current_balance')->find($supplierId);
        if ($this->selectedSupplier) {
            $this->supplierBills = Purchase::select('id', 'purchase_number', 'reference_number', 'purchase_date', 'grand_total', 'paid_amount', 'due_amount', 'status')
                ->where('supplier_id', $supplierId)
                ->where('due_amount', '>', 0)
                ->where('status', 'Confirmed')
                ->orderBy('purchase_date', 'asc')
                ->get();
        }
    }

    public function closeBillsModal()
    {
        $this->selectedSupplier = null;
        $this->supplierBills = [];
    }

    public function render()
    {
        // 1. Direct MySQL aggregate metrics
        $metrics = DB::table('suppliers')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(current_balance), 0) as total_payable,
                COALESCE(SUM(CASE WHEN current_balance > 0 THEN 1 ELSE 0 END), 0) as with_balance_count
            ')->first();

        $totalPayable = (float) ($metrics->total_payable ?? 0);
        $suppliersWithBalance = (int) ($metrics->with_balance_count ?? 0);

        $query = Supplier::select('id', 'supplier_code', 'name', 'company_name', 'contact_person', 'mobile', 'email', 'current_balance', 'status')
            ->where('status', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter_balance === 'with_balance') {
            $query->where('current_balance', '>', 0);
        } elseif ($this->filter_balance === 'zero_balance') {
            $query->where('current_balance', '<=', 0);
        }

        $suppliers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.reports.payables-report', [
            'suppliers' => $suppliers,
            'totalPayable' => $totalPayable,
            'suppliersWithBalance' => $suppliersWithBalance,
        ])->layout('layouts.app', ['title' => 'Accounts Payable Aging & Report']);
    }
}
