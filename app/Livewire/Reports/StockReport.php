<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $category_id_filter = '';
    public $stock_status_filter = '';
    public $perPage = 15;
    public $sortField = 'name';
    public $sortDirection = 'asc';

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
        $this->perPage = 15;
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        // 1. Direct MySQL aggregate metrics
        $stockStats = DB::table('products')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_items,
                COALESCE(SUM(current_stock), 0) as total_qty,
                COALESCE(SUM(current_stock * weighted_cost), 0) as total_valuation,
                COALESCE(SUM(CASE WHEN current_stock <= min_stock THEN 1 ELSE 0 END), 0) as low_stock_count
            ')->first();

        $totalItems = (int) ($stockStats->total_items ?? 0);
        $totalStockQty = (float) ($stockStats->total_qty ?? 0);
        $totalValuation = (float) ($stockStats->total_valuation ?? 0);
        $lowStockCount = (int) ($stockStats->low_stock_count ?? 0);

        // 2. Paginated selective product list
        $query = Product::select('id', 'product_code', 'barcode', 'name', 'category_id', 'unit_id', 'purchase_price', 'sales_price', 'weighted_cost', 'current_stock', 'min_stock', 'image', 'status')
            ->with(['category:id,name', 'unit:id,name'])
            ->where('status', true);

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

        $categories = ProductCategory::select('id', 'name')->where('status', true)->orderBy('name')->get();

        return view('livewire.reports.stock-report', [
            'products' => $products,
            'categories' => $categories,
            'totalItems' => $totalItems,
            'totalStockQty' => $totalStockQty,
            'totalValuation' => $totalValuation,
            'lowStockCount' => $lowStockCount,
        ])->layout('layouts.app', ['title' => 'Stock Valuation & Inventory Report']);
    }
}
