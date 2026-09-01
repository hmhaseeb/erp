<?php

namespace App\Livewire\Purchases;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $purchase_number, $purchase_date, $supplier_id, $reference_number;
    public $payment_type = 'Credit', $account_id;
    public $notes;

    public $items = [];

    // Totals
    public $subtotal = 0, $discount_amount = 0, $vat_amount = 0, $grand_total = 0;

    // Quick Supplier Modal
    public $isSupplierModalOpen = false;
    public $supp_code, $supp_name, $supp_company_name, $supp_contact_person;
    public $supp_mobile, $supp_email, $supp_address, $supp_trn_number;
    public $supp_opening_balance = 0, $supp_payment_terms, $supp_notes;

    // Quick Product Modal (Reuses Product Create fields & layout)
    public $isProductModalOpen = false;
    public $isEditMode = false;
    public $targetProductItemIndex = null;
    public $product_code, $barcode, $name, $category_id, $brand, $unit_id;
    public $purchase_price = 0, $sales_price = 0, $tax_percent = 5, $min_stock = 5;
    public $opening_stock = 0, $warehouse = 'Main Warehouse', $description, $image, $existingImage;

    public function mount()
    {
        $this->purchase_date = now()->toDateString();
        $this->generatePurchaseNumber();

        $firstSupplier = Supplier::first();
        if ($firstSupplier) {
            $this->supplier_id = $firstSupplier->id;
        }

        $firstAccount = Account::first();
        if ($firstAccount) {
            $this->account_id = $firstAccount->id;
        }

        // Add 1 default row
        $this->addItem();
    }

    public function updatedPurchaseDate()
    {
        $this->generatePurchaseNumber();
    }

    public function generatePurchaseNumber()
    {
        $this->purchase_number = \App\Models\InvoiceSetting::getNextPurchaseNumber($this->purchase_date);
    }

    public function addItem()
    {
        $selectedIds = array_filter(array_column($this->items, 'product_id'));
        $firstProd = Product::where('status', true)
            ->whereNotIn('id', $selectedIds)
            ->orderBy('name')
            ->first();

        if (!$firstProd && count($selectedIds) > 0) {
            $this->dispatch('toast', message: 'All available products have already been added to this purchase invoice.', type: 'warning', title: 'Cannot Add More Products');
            return;
        }

        $this->items[] = [
            'product_id' => $firstProd ? $firstProd->id : '',
            'quantity' => 1,
            'unit_price' => $firstProd ? $firstProd->purchase_price : 0,
            'discount_amount' => 0,
            'vat_percent' => $firstProd ? $firstProd->tax_percent : 5,
            'vat_amount' => 0,
            'line_total' => 0,
        ];
        $this->calculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'product_id') {
            $duplicate = false;
            foreach ($this->items as $i => $item) {
                if ($i != $index && !empty($item['product_id']) && $item['product_id'] == $value) {
                    $duplicate = true;
                    break;
                }
            }

            if ($duplicate) {
                $this->items[$index]['product_id'] = '';
                $this->items[$index]['unit_price'] = 0;
                $this->items[$index]['vat_percent'] = 5;
                $this->calculateTotals();

                $dupProd = Product::find($value);
                $dupName = $dupProd ? $dupProd->name : 'Product';
                $this->dispatch('toast', message: "'{$dupName}' is already added to this purchase invoice. Duplicate products are not allowed.", type: 'warning', title: 'Duplicate Item');
                return;
            }

            $prod = Product::find($value);
            if ($prod) {
                $this->items[$index]['unit_price'] = $prod->purchase_price;
                $this->items[$index]['vat_percent'] = $prod->tax_percent ?? 5;
            }
        }

        $this->calculateTotals();
    }

    public function updatedDiscountAmount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->vat_amount = 0;

        foreach ($this->items as $index => $item) {
            $qty = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            $disc = (float)($item['discount_amount'] ?? 0);
            $vatPercent = (float)($item['vat_percent'] ?? 0);

            $itemSubtotal = max(0, ($qty * $price) - $disc);
            $itemVat = ($itemSubtotal * $vatPercent) / 100;
            $lineTotal = $itemSubtotal + $itemVat;

            $this->items[$index]['vat_amount'] = $itemVat;
            $this->items[$index]['line_total'] = $lineTotal;

            $this->subtotal += $itemSubtotal;
            $this->vat_amount += $itemVat;
        }

        $totalDiscount = (float)$this->discount_amount;
        $discountedSubtotal = max(0, $this->subtotal - $totalDiscount);
        $this->grand_total = $discountedSubtotal + $this->vat_amount;
    }

    public function savePurchase(PurchaseService $purchaseService)
    {
        $this->validate([
            'purchase_number' => 'required|string|unique:purchases,purchase_number',
            'purchase_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:Cash,Bank,Credit',
            'account_id' => 'required_if:payment_type,Cash,Bank|nullable|exists:accounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'numeric|min:0',
        ]);

        // Validate duplicates
        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            $msg = 'Duplicate products detected in purchase items. Each product may only appear once.';
            $this->dispatch('toast', message: $msg, type: 'danger', title: 'Validation Error');
            return;
        }

        $header = [
            'purchase_number' => $this->purchase_number,
            'purchase_date' => $this->purchase_date,
            'supplier_id' => $this->supplier_id,
            'reference_number' => $this->reference_number,
            'payment_type' => $this->payment_type,
            'account_id' => $this->account_id,
            'subtotal' => $this->subtotal,
            'discount_amount' => (float)$this->discount_amount,
            'vat_amount' => $this->vat_amount,
            'grand_total' => $this->grand_total,
            'notes' => $this->notes,
        ];

        try {
            $purchaseService->createPurchase($header, $this->items);

            session()->flash('success', "Purchase Invoice #{$this->purchase_number} created successfully.");
            $this->dispatch('toast', message: "Purchase Invoice #{$this->purchase_number} created successfully.", type: 'success', title: 'Purchase Created');
            return redirect()->route('purchases.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger', title: 'Error');
        }
    }

    // ==========================================
    // Quick Add Supplier Methods
    // ==========================================
    public function openSupplierModal()
    {
        $this->resetValidation();
        $this->reset([
            'supp_name', 'supp_company_name', 'supp_contact_person',
            'supp_mobile', 'supp_email', 'supp_address', 'supp_trn_number',
            'supp_opening_balance', 'supp_payment_terms', 'supp_notes'
        ]);
        $setting = \App\Models\GeneralSetting::first();
        $prefix = $setting ? $setting->supplier_prefix : 'SUP-';
        $maxId = Supplier::max('id') + 1;
        $this->supp_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
        $this->isSupplierModalOpen = true;
    }

    public function closeSupplierModal()
    {
        $this->isSupplierModalOpen = false;
        $this->resetValidation();
    }

    public function saveNewSupplier()
    {
        $this->validate([
            'supp_code' => 'required|string|max:50|unique:suppliers,supplier_code',
            'supp_name' => 'required|string|max:255',
            'supp_company_name' => 'nullable|string|max:255',
            'supp_mobile' => 'nullable|string|max:50',
            'supp_email' => 'nullable|email|max:255',
            'supp_opening_balance' => 'numeric|min:0',
        ]);

        $supplier = Supplier::create([
            'supplier_code' => $this->supp_code,
            'name' => $this->supp_name,
            'company_name' => $this->supp_company_name,
            'contact_person' => $this->supp_contact_person,
            'mobile' => $this->supp_mobile,
            'email' => $this->supp_email,
            'address' => $this->supp_address,
            'trn_number' => $this->supp_trn_number,
            'opening_balance' => $this->supp_opening_balance ?: 0,
            'current_balance' => $this->supp_opening_balance ?: 0,
            'payment_terms' => $this->supp_payment_terms,
            'notes' => $this->supp_notes,
            'status' => true,
        ]);

        $this->supplier_id = $supplier->id;
        $this->isSupplierModalOpen = false;
        $this->dispatch('toast', message: "Supplier '{$supplier->name}' registered and selected successfully.", type: 'success', title: 'Supplier Added');
    }

    // ==========================================
    // Quick Add Product Methods (Reusing Product Form)
    // ==========================================
    public function openProductModal($targetIndex = null)
    {
        $this->resetValidation();
        $this->targetProductItemIndex = $targetIndex;
        $this->reset([
            'barcode', 'name', 'category_id', 'brand', 'unit_id',
            'purchase_price', 'sales_price', 'description', 'image', 'existingImage'
        ]);
        $this->tax_percent = 5;
        $this->min_stock = 5;
        $this->opening_stock = 0;
        $this->warehouse = 'Main Warehouse';

        $setting = \App\Models\GeneralSetting::first();
        $prefix = $setting ? $setting->product_prefix : 'PROD-';
        $maxId = Product::max('id') + 1;
        $this->product_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
        $this->isProductModalOpen = true;
    }

    public function closeProductModal()
    {
        $this->isProductModalOpen = false;
        $this->targetProductItemIndex = null;
        $this->image = null;
        $this->existingImage = null;
        $this->resetValidation();
    }

    public function removeImage()
    {
        $this->image = null;
        $this->existingImage = null;
    }

    public function saveNewProduct(\App\Services\StockService $stockService)
    {
        $this->validate([
            'product_code' => 'required|string|max:100|unique:products,product_code',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'tax_percent' => 'numeric|min:0',
            'opening_stock' => 'numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
        }

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
            'min_stock' => $this->min_stock ?: 5,
            'opening_stock' => $this->opening_stock ?: 0,
            'current_stock' => 0,
            'weighted_cost' => $this->purchase_price,
            'warehouse' => $this->warehouse ?: 'Main Warehouse',
            'image' => $imagePath,
            'description' => $this->description,
            'status' => true,
        ]);

        if ($this->opening_stock > 0) {
            $stockService->recordMovement(
                $product->id,
                now()->toDateString(),
                'OPENING',
                (float)$this->opening_stock,
                0,
                (float)$this->purchase_price,
                Product::class,
                $product->id,
                'Initial Opening Stock'
            );
            $product->refresh();
        }

        // Auto-select in active target row or append as a new item row
        if ($this->targetProductItemIndex !== null && isset($this->items[$this->targetProductItemIndex])) {
            $idx = $this->targetProductItemIndex;
            $this->items[$idx]['product_id'] = $product->id;
            $this->items[$idx]['unit_price'] = (float)$product->purchase_price;
            $this->items[$idx]['vat_percent'] = (float)$product->tax_percent;
            $this->calculateTotals();
        } else {
            // Check if there is an empty row
            $emptyIndex = null;
            foreach ($this->items as $i => $item) {
                if (empty($item['product_id'])) {
                    $emptyIndex = $i;
                    break;
                }
            }

            if ($emptyIndex !== null) {
                $this->items[$emptyIndex]['product_id'] = $product->id;
                $this->items[$emptyIndex]['unit_price'] = (float)$product->purchase_price;
                $this->items[$emptyIndex]['vat_percent'] = (float)$product->tax_percent;
                $this->calculateTotals();
            } else {
                $this->items[] = [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => (float)$product->purchase_price,
                    'discount_amount' => 0,
                    'vat_percent' => (float)$product->tax_percent,
                    'vat_amount' => 0,
                    'line_total' => 0,
                ];
                $this->calculateTotals();
            }
        }

        $this->isProductModalOpen = false;
        $this->targetProductItemIndex = null;
        $this->image = null;
        $this->existingImage = null;
        $this->dispatch('toast', message: "Product '{$product->name}' registered and selected successfully.", type: 'success', title: 'Product Added');
    }

    public function render()
    {
        $suppliers = Supplier::select('id', 'name', 'company_name', 'current_balance')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $accounts = Account::select('id', 'name', 'type', 'current_balance')
            ->where('status', true)
            ->get();

        $products = Product::select('id', 'name', 'category_id', 'product_code', 'current_stock', 'sales_price', 'purchase_price', 'tax_percent')
            ->with('category:id,name')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        // Lazy-load categories and units ONLY when the quick product modal is opened
        $categories = $this->isProductModalOpen 
            ? \App\Models\ProductCategory::select('id', 'name')->orderBy('name')->get() 
            : collect();

        $units = $this->isProductModalOpen 
            ? \App\Models\Unit::select('id', 'name')->orderBy('name')->get() 
            : collect();

        return view('livewire.purchases.create', [
            'suppliers' => $suppliers,
            'accounts' => $accounts,
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
        ])->layout('layouts.app', ['title' => 'Create Purchase Invoice']);
    }
}
