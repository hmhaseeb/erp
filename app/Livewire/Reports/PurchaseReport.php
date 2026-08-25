<?php

namespace App\Livewire\Reports;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $start_date;
    public $end_date;
    public $supplier_id_filter = '';
    public $payment_type_filter = '';
    public $perPage = 15;
    public $sortField = 'purchase_date';
    public $sortDirection = 'desc';

    public function mount()
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
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

    public function updatedSupplierIdFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentTypeFilter()
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
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
        $this->reset(['search', 'supplier_id_filter', 'payment_type_filter']);
        $this->perPage = 15;
        $this->sortField = 'purchase_date';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $query = Purchase::select('id', 'purchase_number', 'reference_number', 'purchase_date', 'supplier_id', 'payment_type', 'subtotal', 'vat_amount', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with('supplier:id,name,company_name')
            ->whereBetween('purchase_date', [$this->start_date, $this->end_date])
            ->where('status', 'Confirmed');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('purchase_number', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                  ->orWhere('payment_type', 'like', '%' . $this->search . '%')
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

        // Single MySQL aggregate query for KPI summary
        $metricsQuery = clone $query;
        $metrics = $metricsQuery->selectRaw('
            COUNT(*) as total_bills,
            COALESCE(SUM(grand_total), 0) as total_purchases,
            COALESCE(SUM(vat_amount), 0) as total_vat
        ')->first();

        $totalPurchases = (float) ($metrics->total_purchases ?? 0);
        $totalVat = (float) ($metrics->total_vat ?? 0);
        $totalBills = (int) ($metrics->total_bills ?? 0);
        $avgBill = $totalBills > 0 ? $totalPurchases / $totalBills : 0;

        $purchases = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $suppliers = Supplier::select('id', 'name', 'company_name')->where('status', true)->orderBy('name')->get();

        return view('livewire.reports.purchase-report', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'totalPurchases' => $totalPurchases,
            'totalVat' => $totalVat,
            'totalBills' => $totalBills,
            'avgBill' => $avgBill,
        ])->layout('layouts.app', ['title' => 'Purchase Report & Statement']);
    }
}
