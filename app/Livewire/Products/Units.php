<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class Units extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $unitId;
    public $isEditMode = false;
    public $name, $code;
    public $isModalOpen = false;

    public function updatedSearch()
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
        $this->perPage = 10;
        $this->resetPage();
    }

    protected function rules()
    {
        $uniqueName = 'required|string|max:100|unique:units,name,' . ($this->unitId ?? 'NULL') . ',id';
        return [
            'name' => $uniqueName,
            'code' => 'nullable|string|max:50',
        ];
    }

    public function openModal()
    {
        $this->reset(['unitId', 'isEditMode', 'name', 'code']);
        $this->isModalOpen = true;
    }

    public function editUnit($id)
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->isEditMode = true;
        $this->name = $unit->name;
        $this->code = $unit->code;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveUnit()
    {
        $this->validate();

        if ($this->isEditMode && $this->unitId) {
            $unit = Unit::findOrFail($this->unitId);
            $unit->update([
                'name' => strtoupper($this->name),
                'code' => strtoupper($this->code ?? $this->name),
            ]);

            session()->flash('success', "Unit '{$this->name}' updated successfully.");
        } else {
            Unit::create([
                'name' => strtoupper($this->name),
                'code' => strtoupper($this->code ?? $this->name),
            ]);

            session()->flash('success', "Unit '{$this->name}' added successfully.");
        }

        $this->closeModal();
    }

    public function deleteUnit($id)
    {
        $unit = Unit::findOrFail($id);
        $assignedCount = Product::where('unit_id', $unit->id)->count();
        if ($assignedCount > 0) {
            session()->flash('error', "Cannot delete unit '{$unit->name}' because {$assignedCount} products are using it.");
            return;
        }

        $unit->delete();
        session()->flash('success', 'Unit deleted successfully.');
    }

    public function render()
    {
        $query = Unit::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $units = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.products.units', ['units' => $units])
            ->layout('layouts.app', ['title' => 'Measurement Units']);
    }
}
