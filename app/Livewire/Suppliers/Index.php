<?php

namespace App\Livewire\Suppliers;

use App\Models\Purchase;
use App\Models\Supplier;
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

    public $supplierId;
    public $isEditMode = false;
    public $supplier_code, $name, $company_name, $contact_person, $mobile, $email, $address, $trn_number;
    public $opening_balance = 0, $payment_terms, $notes;
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
        $uniqueCode = 'required|string|max:50|unique:suppliers,supplier_code,' . ($this->supplierId ?? 'NULL') . ',id';
        return [
            'supplier_code' => $uniqueCode,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'opening_balance' => 'numeric|min:0',
        ];
    }

    public function mount()
    {
        $this->generateCode();
    }

    public function generateCode()
    {
        $setting = \App\Models\GeneralSetting::first();
        $prefix = $setting ? $setting->supplier_prefix : 'SUP-';
        $maxId = Supplier::max('id') + 1;
        $this->supplier_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
    }

    public function openModal()
    {
        $this->reset(['supplierId', 'isEditMode', 'name', 'company_name', 'contact_person', 'mobile', 'email', 'address', 'trn_number', 'opening_balance', 'payment_terms', 'notes']);
        $this->generateCode();
        $this->isModalOpen = true;
    }

    public function editSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->isEditMode = true;
        $this->supplier_code = $supplier->supplier_code;
        $this->name = $supplier->name;
        $this->company_name = $supplier->company_name;
        $this->contact_person = $supplier->contact_person;
        $this->mobile = $supplier->mobile;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->trn_number = $supplier->trn_number;
        $this->opening_balance = $supplier->opening_balance;
        $this->payment_terms = $supplier->payment_terms;
        $this->notes = $supplier->notes;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveSupplier()
    {
        $this->validate();

        if ($this->isEditMode && $this->supplierId) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update([
                'supplier_code' => $this->supplier_code,
                'name' => $this->name,
                'company_name' => $this->company_name,
                'contact_person' => $this->contact_person,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'address' => $this->address,
                'trn_number' => $this->trn_number,
                'payment_terms' => $this->payment_terms,
                'notes' => $this->notes,
            ]);

            session()->flash('success', "Supplier '{$this->name}' updated successfully.");
        } else {
            Supplier::create([
                'supplier_code' => $this->supplier_code,
                'name' => $this->name,
                'company_name' => $this->company_name,
                'contact_person' => $this->contact_person,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'address' => $this->address,
                'trn_number' => $this->trn_number,
                'opening_balance' => $this->opening_balance,
                'current_balance' => $this->opening_balance,
                'payment_terms' => $this->payment_terms,
                'notes' => $this->notes,
                'status' => true,
            ]);

            session()->flash('success', "Supplier '{$this->name}' registered successfully.");
        }

        $this->closeModal();
    }

    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        if ($supplier->current_balance != 0) {
            session()->flash('error', "Cannot delete supplier with an outstanding balance (AED {$supplier->current_balance}).");
            return;
        }

        $hasPurchases = Purchase::where('supplier_id', $supplier->id)->exists();
        if ($hasPurchases) {
            session()->flash('error', "Cannot delete supplier with recorded purchase invoices.");
            return;
        }

        $supplier->delete();
        session()->flash('success', 'Supplier deleted successfully.');
    }

    public function render()
    {
        $query = Supplier::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_code', 'like', '%' . $this->search . '%')
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

        $suppliers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.suppliers.index', ['suppliers' => $suppliers])
            ->layout('layouts.app', ['title' => 'Suppliers Directory']);
    }
}
