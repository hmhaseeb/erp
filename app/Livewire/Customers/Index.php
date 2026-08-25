<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status_filter = '';
    public $balance_filter = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $customerId;
    public $isEditMode = false;
    public $customer_code, $name, $company_name, $contact_person, $mobile, $email, $address, $trn_number;
    public $opening_balance = 0, $credit_limit = 0, $payment_terms, $notes;
    public $isModalOpen = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedBalanceFilter()
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
        $this->reset(['search', 'status_filter', 'balance_filter']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    protected function rules()
    {
        $uniqueCode = 'required|string|max:50|unique:customers,customer_code,' . ($this->customerId ?? 'NULL') . ',id';
        return [
            'customer_code' => $uniqueCode,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'opening_balance' => 'numeric|min:0',
            'credit_limit' => 'numeric|min:0',
        ];
    }

    public function mount()
    {
        $this->generateCode();
    }

    public function generateCode()
    {
        $setting = \App\Models\GeneralSetting::first();
        $prefix = $setting ? $setting->customer_prefix : 'CUST-';
        $maxId = Customer::max('id') + 1;
        $this->customer_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
    }

    public function openModal()
    {
        $this->reset(['customerId', 'isEditMode', 'name', 'company_name', 'contact_person', 'mobile', 'email', 'address', 'trn_number', 'opening_balance', 'credit_limit', 'payment_terms', 'notes']);
        $this->generateCode();
        $this->isModalOpen = true;
    }

    public function editCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->isEditMode = true;
        $this->customer_code = $customer->customer_code;
        $this->name = $customer->name;
        $this->company_name = $customer->company_name;
        $this->contact_person = $customer->contact_person;
        $this->mobile = $customer->mobile;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->trn_number = $customer->trn_number;
        $this->opening_balance = $customer->opening_balance;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->notes = $customer->notes;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveCustomer()
    {
        $this->validate();

        if ($this->isEditMode && $this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            $customer->update([
                'customer_code' => $this->customer_code,
                'name' => $this->name,
                'company_name' => $this->company_name,
                'contact_person' => $this->contact_person,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'address' => $this->address,
                'trn_number' => $this->trn_number,
                'credit_limit' => $this->credit_limit,
                'payment_terms' => $this->payment_terms,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Customer '{$this->name}' updated successfully.");
        } else {
            Customer::create([
                'customer_code' => $this->customer_code,
                'name' => $this->name,
                'company_name' => $this->company_name,
                'contact_person' => $this->contact_person,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'address' => $this->address,
                'trn_number' => $this->trn_number,
                'opening_balance' => $this->opening_balance,
                'current_balance' => $this->opening_balance,
                'credit_limit' => $this->credit_limit,
                'payment_terms' => $this->payment_terms,
                'notes' => $this->notes,
                'status' => true,
            ]);

            session()->flash('success', "Customer '{$this->name}' registered successfully.");
        }

        $this->closeModal();
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        if ($customer->current_balance != 0) {
            session()->flash('error', "Cannot delete customer with an outstanding balance (AED {$customer->current_balance}).");
            return;
        }

        $hasSales = Sale::where('customer_id', $customer->id)->exists();
        if ($hasSales) {
            session()->flash('error', "Cannot delete customer with recorded sales invoices.");
            return;
        }

        $customer->delete();
        session()->flash('success', 'Customer deleted successfully.');
    }

    public function render()
    {
        $query = Customer::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_code', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status_filter !== '') {
            $query->where('status', (bool)$this->status_filter);
        }

        if ($this->balance_filter === 'has_balance') {
            $query->where('current_balance', '>', 0);
        } elseif ($this->balance_filter === 'zero_balance') {
            $query->where('current_balance', '<=', 0);
        }

        $customers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.customers.index', ['customers' => $customers])
            ->layout('layouts.app', ['title' => 'Customers Directory']);
    }
}
