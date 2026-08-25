<?php

namespace App\Livewire\Products;

use App\Models\ProductCategory;
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
    public $name, $code, $description;
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
        $uniqueName = 'required|string|max:255|unique:product_categories,name,' . ($this->categoryId ?? 'NULL') . ',id';
        return [
            'name' => $uniqueName,
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function openModal()
    {
        $this->reset(['categoryId', 'isEditMode', 'name', 'code', 'description']);
        $this->isModalOpen = true;
    }

    public function editCategory($id)
    {
        $category = ProductCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->isEditMode = true;
        $this->name = $category->name;
        $this->code = $category->code;
        $this->description = $category->description;
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
            $category = ProductCategory::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
            ]);

            session()->flash('success', "Category '{$this->name}' updated successfully.");
        } else {
            ProductCategory::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'status' => true,
            ]);

            session()->flash('success', "Product Category '{$this->name}' created successfully.");
        }

        $this->closeModal();
    }

    public function deleteCategory($id)
    {
        $category = ProductCategory::withCount('products')->findOrFail($id);
        if ($category->products_count > 0) {
            session()->flash('error', "Cannot delete category '{$category->name}' because it contains {$category->products_count} assigned products.");
            return;
        }

        $category->delete();
        session()->flash('success', 'Product category deleted successfully.');
    }

    public function render()
    {
        $query = ProductCategory::withCount('products');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $categories = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        return view('livewire.products.categories', ['categories' => $categories])
            ->layout('layouts.app', ['title' => 'Product Categories']);
    }
}
