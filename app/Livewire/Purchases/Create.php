<?php

namespace App\Livewire\Purchases;

use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Livewire\Component;

class Create extends Component
{
    public $purchase_number, $purchase_date, $supplier_id, $reference_number;
    public $payment_type = 'Credit', $account_id;
    public $notes;

    public $items = [];

    // Totals
    public $subtotal = 0, $discount_amount = 0, $vat_amount = 0, $grand_total = 0;

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
        $firstProd = Product::first();
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
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->calculateTotals();
        }
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = $parts[0];
            $field = $parts[1];

            if ($field === 'product_id') {
                $prod = Product::find($value);
                if ($prod) {
                    $this->items[$index]['unit_price'] = $prod->purchase_price;
                    $this->items[$index]['vat_percent'] = $prod->tax_percent;
                }
            }
        }
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->vat_amount = 0;
        $this->grand_total = 0;

        foreach ($this->items as $idx => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $disc = (float) ($item['discount_amount'] ?? 0);
            $vatPct = (float) ($item['vat_percent'] ?? 0);

            $lineSubtotal = max(0, ($qty * $price) - $disc);
            $lineVat = $lineSubtotal * ($vatPct / 100);
            $lineTotal = $lineSubtotal + $lineVat;

            $this->items[$idx]['vat_amount'] = round($lineVat, 2);
            $this->items[$idx]['line_total'] = round($lineTotal, 2);

            $this->subtotal += $lineSubtotal;
            $this->vat_amount += $lineVat;
        }

        $this->subtotal = round($this->subtotal, 2);
        $this->vat_amount = round($this->vat_amount, 2);
        $this->grand_total = round($this->subtotal + $this->vat_amount - (float)$this->discount_amount, 2);
    }

    public function savePurchase(PurchaseService $purchaseService)
    {
        $this->validate([
            'purchase_number' => 'required|unique:purchases,purchase_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'payment_type' => 'required|in:Cash,Bank,Credit',
            'account_id' => 'required_if:payment_type,Cash,Bank',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

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

    public function render()
    {
        $suppliers = Supplier::select('id', 'name', 'company_name')->where('status', true)->orderBy('name')->get();
        $accounts = Account::select('id', 'name', 'type', 'current_balance')->where('status', true)->get();
        $products = Product::with('category')->where('status', true)->orderBy('name')->get();

        return view('livewire.purchases.create', [
            'suppliers' => $suppliers,
            'accounts' => $accounts,
            'products' => $products,
        ])->layout('layouts.app', ['title' => 'Create Purchase Invoice']);
    }
}
