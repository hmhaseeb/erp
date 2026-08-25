<?php

namespace App\Livewire\Income;

use App\Models\Income;
use App\Models\IncomeCategory;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $categoryId;
    public $isEditMode = false;
    public $name, $description;
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
        $uniqueName = 'required|string|max:255|unique:income_categories,name,' . ($this->categoryId ?? 'NULL') . ',id';
        return [
            'name' => $uniqueName,
            'description' => 'nullable|string|max:500',
        ];
    }

    public function openModal()
    {
        $this->reset(['categoryId', 'isEditMode', 'name', 'description']);
        $this->isModalOpen = true;
    }

    public function editCategory($id)
    {
        $cat = IncomeCategory::findOrFail($id);
        $this->categoryId = $cat->id;
        $this->isEditMode = true;
        $this->name = $cat->name;
        $this->description = $cat->description;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveCategory()
    {
        $this->validate();

        if ($this->isEditMode && $this->categoryId) {
            $cat = IncomeCategory::findOrFail($this->categoryId);
            $cat->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            session()->flash('success', "Income Category '{$this->name}' updated successfully.");
        } else {
            IncomeCategory::create([
                'name' => $this->name,
                'description' => $this->description,
                'status' => true,
            ]);

            session()->flash('success', "Income Category '{$this->name}' created successfully.");
        }

        $this->closeModal();
    }

    public function deleteCategory($id)
    {
        $cat = IncomeCategory::findOrFail($id);
        $assignedCount = Income::where('income_category_id', $cat->id)->count();
        if ($assignedCount > 0) {
            session()->flash('error', "Cannot delete category '{$cat->name}' because {$assignedCount} income records are linked to it.");
            return;
        }

        $cat->delete();
        session()->flash('success', 'Income category deleted successfully.');
    }

    public function render()
    {
        $query = IncomeCategory::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $categories = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.income.categories', ['categories' => $categories])
            ->layout('layouts.app', ['title' => 'Income Categories']);
    }
}
