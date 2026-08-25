<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\StockService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $category_id_filter = '';
    public $stock_status_filter = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    // Form fields
    public $productId;
    public $isEditMode = false;
    public $product_code, $barcode, $name, $category_id, $brand, $unit_id;
    public $purchase_price = 0, $sales_price = 0, $tax_percent = 5, $min_stock = 5;
    public $opening_stock = 0, $warehouse = 'Main Warehouse', $description;
    public $image;
    public $existingImage;
    public $isModalOpen = false;

    // View single product modal
    public $viewProduct = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryIdFilter()
    {
        $this->resetPage();
    }

    public function updatedStockStatusFilter()
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
        $this->reset(['search', 'category_id_filter', 'stock_status_filter']);
        $this->perPage = 10;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    protected function rules()
    {
        $uniqueCode = 'required|string|max:100|unique:products,product_code,' . ($this->productId ?? 'NULL') . ',id';
        $uniqueBarcode = 'nullable|string|max:100|unique:products,barcode,' . ($this->productId ?? 'NULL') . ',id';

        return [
            'product_code' => $uniqueCode,
            'barcode' => $uniqueBarcode,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'tax_percent' => 'numeric|min:0',
            'min_stock' => 'numeric|min:0',
            'opening_stock' => 'numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];
    }

    public function mount()
    {
        $this->generateCode();
    }

    public function generateCode()
    {
        $setting = \App\Models\GeneralSetting::first();
        $prefix = $setting ? $setting->product_prefix : 'PROD-';
        $maxId = Product::max('id') + 1;
        $this->product_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
    }

    public function showProductDetails($id)
    {
        $this->viewProduct = Product::with(['category', 'unit', 'stockMovements' => function($q) {
            $q->orderBy('date', 'desc')->orderBy('id', 'desc')->take(6);
        }])->find($id);
    }

    public function closeViewModal()
    {
        $this->viewProduct = null;
    }

    public function openModal()
    {
        $this->reset(['productId', 'isEditMode', 'barcode', 'name', 'category_id', 'brand', 'unit_id', 'purchase_price', 'sales_price', 'description', 'image', 'existingImage']);
        $this->tax_percent = 5;
        $this->min_stock = 5;
        $this->opening_stock = 0;
        $this->warehouse = 'Main Warehouse';
        $this->generateCode();
        $this->isModalOpen = true;
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->isEditMode = true;
        $this->product_code = $product->product_code;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->brand = $product->brand;
        $this->unit_id = $product->unit_id;
        $this->purchase_price = $product->purchase_price;
        $this->sales_price = $product->sales_price;
        $this->tax_percent = $product->tax_percent;
        $this->min_stock = $product->min_stock;
        $this->opening_stock = $product->opening_stock;
        $this->warehouse = $product->warehouse;
        $this->description = $product->description;
        $this->existingImage = $product->image;
        $this->image = null;
        $this->viewProduct = null;
        $this->isModalOpen = true;
    }

    public function removeImage()
    {
        $this->image = null;
        $this->existingImage = null;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->image = null;
        $this->existingImage = null;
        $this->resetValidation();
    }

    public function saveProduct(StockService $stockService)
    {
        $this->validate();

        $imagePath = $this->existingImage;
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
        }

        if ($this->isEditMode && $this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update([
                'product_code' => $this->product_code,
                'barcode' => $this->barcode,
                'name' => $this->name,
                'category_id' => $this->category_id ?: null,
                'brand' => $this->brand,
                'unit_id' => $this->unit_id ?: null,
                'purchase_price' => $this->purchase_price,
                'sales_price' => $this->sales_price,
                'tax_percent' => $this->tax_percent,
                'min_stock' => $this->min_stock,
                'warehouse' => $this->warehouse,
                'image' => $imagePath,
                'description' => $this->description,
            ]);

            session()->flash('success', "Product '{$this->name}' updated successfully.");
        } else {
            $product = Product::create([
                'product_code' => $this->product_code,
                'barcode' => $this->barcode,
                'name' => $this->name,
                'category_id' => $this->category_id ?: null,
                'brand' => $this->brand,
                'unit_id' => $this->unit_id ?: null,
                'purchase_price' => $this->purchase_price,
                'sales_price' => $this->sales_price,
                'tax_percent' => $this->tax_percent,
                'min_stock' => $this->min_stock,
                'opening_stock' => $this->opening_stock,
                'current_stock' => 0,
                'weighted_cost' => $this->purchase_price,
                'warehouse' => $this->warehouse,
                'image' => $imagePath,
                'description' => $this->description,
                'status' => true,
            ]);

            if ($this->opening_stock > 0) {
                $stockService->recordMovement(
                    $product->id,
                    now()->toDateString(),
                    'OPENING',
                    (float) $this->opening_stock,
                    0,
                    (float) $this->purchase_price,
                    Product::class,
                    $product->id,
                    'Initial Opening Stock'
                );
            }

            session()->flash('success', "Product '{$this->name}' registered successfully.");
        }

        $this->closeModal();
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        if ($product->current_stock > 0) {
            session()->flash('error', "Cannot delete product with existing stock balance ({$product->current_stock}).");
            return;
        }

        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
    }

    public function render()
    {
        $query = Product::with(['category', 'unit']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('product_code', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category_id_filter) {
            $query->where('category_id', $this->category_id_filter);
        }

        if ($this->stock_status_filter === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
        } elseif ($this->stock_status_filter === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($this->stock_status_filter === 'in_stock') {
            $query->whereColumn('current_stock', '>', 'min_stock');
        }

        $products = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $categories = ProductCategory::all();
        $units = Unit::all();

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
        ])->layout('layouts.app', ['title' => 'Products Directory']);
    }
}
