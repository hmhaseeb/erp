<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $start_date;
    public $end_date;
    public $customer_id_filter = '';
    public $payment_type_filter = '';
    public $perPage = 15;
    public $sortField = 'sale_date';
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

    public function updatedCustomerIdFilter()
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
        $this->reset(['search', 'customer_id_filter', 'payment_type_filter']);
        $this->perPage = 15;
        $this->sortField = 'sale_date';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $query = Sale::select('id', 'invoice_number', 'sale_date', 'customer_id', 'payment_type', 'subtotal', 'vat_amount', 'grand_total', 'paid_amount', 'due_amount', 'status')
            ->with('customer:id,name,company_name')
            ->whereBetween('sale_date', [$this->start_date, $this->end_date])
            ->where('status', 'Confirmed');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('payment_type', 'like', '%' . $this->search . '%')
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

        // Single MySQL aggregate query for KPI summary
        $metricsQuery = clone $query;
        $metrics = $metricsQuery->selectRaw('
            COUNT(*) as total_invoices,
            COALESCE(SUM(grand_total), 0) as total_sales,
            COALESCE(SUM(vat_amount), 0) as total_vat
        ')->first();

        $totalSales = (float) ($metrics->total_sales ?? 0);
        $totalVat = (float) ($metrics->total_vat ?? 0);
        $totalInvoices = (int) ($metrics->total_invoices ?? 0);
        $avgInvoice = $totalInvoices > 0 ? $totalSales / $totalInvoices : 0;

        $sales = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $customers = Customer::select('id', 'name', 'company_name')->where('status', true)->orderBy('name')->get();

        return view('livewire.reports.sales-report', [
            'sales' => $sales,
            'customers' => $customers,
            'totalSales' => $totalSales,
            'totalVat' => $totalVat,
            'totalInvoices' => $totalInvoices,
            'avgInvoice' => $avgInvoice,
        ])->layout('layouts.app', ['title' => 'Sales Statement & Report']);
    }
}
