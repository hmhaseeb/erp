<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Livewire\Component;
use Livewire\WithPagination;

class Stock extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $movement_type_filter = '';
    public $product_id_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $perPage = 15;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    // Adjustment Form fields
    public $product_id, $movement_type = 'ADJUSTMENT_IN', $quantity = 1, $unit_cost = 0, $date, $notes;
    public $isModalOpen = false;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'movement_type' => 'required|in:ADJUSTMENT_IN,ADJUSTMENT_OUT',
        'quantity' => 'required|numeric|gt:0',
        'unit_cost' => 'required|numeric|min:0',
        'date' => 'required|date',
    ];

    public function mount()
    {
        $this->date = now()->toDateString();
        $first = Product::first();
        if ($first) {
            $this->product_id = $first->id;
            $this->unit_cost = $first->weighted_cost;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedMovementTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedProductIdFilter()
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
        $this->reset(['search', 'movement_type_filter', 'product_id_filter', 'date_from', 'date_to']);
        $this->perPage = 15;
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function updatedProductId($val)
    {
        $prod = Product::find($val);
        if ($prod) {
            $this->unit_cost = $prod->weighted_cost;
        }
    }

    public function openModal()
    {
        $this->reset(['quantity', 'notes']);
        $this->date = now()->toDateString();
        $this->movement_type = 'ADJUSTMENT_IN';
        $first = Product::first();
        if ($first) {
            $this->product_id = $first->id;
            $this->unit_cost = $first->weighted_cost;
        }
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveAdjustment(StockService $stockService)
    {
        $this->validate();

        $qtyIn = $this->movement_type === 'ADJUSTMENT_IN' ? (float) $this->quantity : 0;
        $qtyOut = $this->movement_type === 'ADJUSTMENT_OUT' ? (float) $this->quantity : 0;

        $stockService->recordMovement(
            $this->product_id,
            $this->date,
            $this->movement_type,
            $qtyIn,
            $qtyOut,
            (float) $this->unit_cost,
            'Adjustment',
            null,
            $this->notes ?? 'Manual Stock Adjustment'
        );

        session()->flash('success', 'Stock adjustment recorded successfully.');
        $this->closeModal();
    }

    public function render()
    {
        $query = StockMovement::with('product');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('product_code', 'like', '%' . $this->search . '%');
                })
                ->orWhere('movement_type', 'like', '%' . $this->search . '%')
                ->orWhere('notes', 'like', '%' . $this->search . '%')
                ->orWhere('reference_type', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->movement_type_filter) {
            $query->where('movement_type', $this->movement_type_filter);
        }

        if ($this->product_id_filter) {
            $query->where('product_id', $this->product_id_filter);
        }

        if ($this->date_from) {
            $query->whereDate('date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('date', '<=', $this->date_to);
        }

        $movements = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate((int)$this->perPage);

        $products = Product::orderBy('name')->get();

        return view('livewire.products.stock', [
            'products' => $products,
            'movements' => $movements,
        ])->layout('layouts.app', ['title' => 'Stock Movements & Adjustments']);
    }
}
