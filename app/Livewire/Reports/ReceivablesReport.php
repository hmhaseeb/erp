<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReceivablesReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filter_balance = 'with_balance';
    public $perPage = 15;
    public $sortField = 'current_balance';
    public $sortDirection = 'desc';

    public $selectedCustomer = null;
    public $customerInvoices = [];

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

    public function viewCustomerInvoices($customerId)
    {
        $this->selectedCustomer = Customer::select('id', 'name', 'company_name', 'mobile', 'current_balance')->find($customerId);
        if ($this->selectedCustomer) {
            $this->customerInvoices = Sale::select('id', 'invoice_number', 'sale_date', 'grand_total', 'paid_amount', 'due_amount', 'status')
                ->where('customer_id', $customerId)
                ->where('due_amount', '>', 0)
                ->where('status', 'Confirmed')
                ->orderBy('sale_date', 'asc')
                ->get();
        }
    }

    public function closeInvoicesModal()
    {
        $this->selectedCustomer = null;
        $this->customerInvoices = [];
    }

    public function render()
    {
        // 1. Direct MySQL aggregate metrics
        $metrics = DB::table('customers')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->selectRaw('
                COALESCE(SUM(current_balance), 0) as total_receivable,
                COALESCE(SUM(CASE WHEN current_balance > 0 THEN 1 ELSE 0 END), 0) as with_balance_count
            ')->first();

        $totalReceivable = (float) ($metrics->total_receivable ?? 0);
        $customersWithBalance = (int) ($metrics->with_balance_count ?? 0);

        $query = Customer::select('id', 'customer_code', 'name', 'company_name', 'contact_person', 'mobile', 'email', 'current_balance', 'credit_limit', 'status')
            ->where('status', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter_balance === 'with_balance') {
            $query->where('current_balance', '>', 0);
        } elseif ($this->filter_balance === 'zero_balance') {
            $query->where('current_balance', '<=', 0);
        }

        $customers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.reports.receivables-report', [
            'customers' => $customers,
            'totalReceivable' => $totalReceivable,
            'customersWithBalance' => $customersWithBalance,
        ])->layout('layouts.app', ['title' => 'Accounts Receivable Aging & Report']);
    }
}
