<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Services\ReturnService;
use Livewire\Component;
use Livewire\WithPagination;

class Returns extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $customer_id_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $return_number, $return_date, $customer_id, $sale_id, $account_id, $return_reason;
    public $product_id, $quantity = 1, $unit_price = 0, $vat_percent = 5;
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCustomerIdFilter()
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
        $this->reset(['search', 'customer_id_filter', 'date_from', 'date_to']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function mount()
    {
        $this->return_date = now()->toDateString();
        $this->generateReturnNumber();

        $firstCustomer = Customer::first();
        if ($firstCustomer) {
            $this->customer_id = $firstCustomer->id;
        }

        $firstProd = Product::first();
        if ($firstProd) {
            $this->product_id = $firstProd->id;
            $this->unit_price = $firstProd->sales_price;
            $this->vat_percent = $firstProd->tax_percent;
        }
    }

    public function updatedProductId($val)
    {
        $prod = Product::find($val);
        if ($prod) {
            $this->unit_price = $prod->sales_price;
            $this->vat_percent = $prod->tax_percent;
        }
    }

    public function generateReturnNumber()
    {
        $setting = \App\Models\InvoiceSetting::first();
        $prefix = $setting ? $setting->sales_return_prefix : 'SR-';
        $maxId = SalesReturn::max('id') + 1;
        $this->return_number = $prefix . str_pad((string)$maxId, 6, '0', STR_PAD_LEFT);
    }

    public function openModal()
    {
        $this->reset(['return_reason', 'quantity']);
        $this->quantity = 1;
        $this->return_date = now()->toDateString();
        $this->generateReturnNumber();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveReturn(ReturnService $returnService)
    {
        $this->validate([
            'return_number' => 'required|unique:sales_returns,return_number',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|gt:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = $this->quantity * $this->unit_price;
        $vat = $subtotal * ($this->vat_percent / 100);
        $grandTotal = $subtotal + $vat;

        $header = [
            'return_number' => $this->return_number,
            'return_date' => $this->return_date,
            'customer_id' => $this->customer_id,
            'sale_id' => $this->sale_id ?: null,
            'account_id' => $this->account_id ?: null,
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vat, 2),
            'grand_total' => round($grandTotal, 2),
            'return_reason' => $this->return_reason,
        ];

        $items = [
            [
                'product_id' => $this->product_id,
                'quantity' => (float)$this->quantity,
                'unit_price' => (float)$this->unit_price,
                'vat_percent' => (float)$this->vat_percent,
                'vat_amount' => round($vat, 2),
                'line_total' => round($grandTotal, 2),
            ]
        ];

        $returnService->processSalesReturn($header, $items);

        session()->flash('success', "Sales Return #{$this->return_number} processed successfully.");
        $this->closeModal();
    }

    public function render()
    {
        $query = SalesReturn::with(['customer', 'items.product', 'account']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('return_number', 'like', '%' . $this->search . '%')
                  ->orWhere('return_reason', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->customer_id_filter) {
            $query->where('customer_id', $this->customer_id_filter);
        }

        if ($this->date_from) {
            $query->whereDate('return_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('return_date', '<=', $this->date_to);
        }

        $returns = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $customers = Customer::orderBy('name')->get();
        $products = Product::where('status', true)->orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();

        return view('livewire.sales.returns', [
            'customers' => $customers,
            'products' => $products,
            'accounts' => $accounts,
            'returns' => $returns,
        ])->layout('layouts.app', ['title' => 'Sales Returns']);
    }
}
