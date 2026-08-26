<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use Livewire\Component;

class Create extends Component
{
    public $invoice_number, $sale_date, $customer_id;
    public $payment_type = 'Cash', $account_id;
    public $notes;

    public $items = [];

    // Totals
    public $subtotal = 0, $discount_amount = 0, $vat_amount = 0, $grand_total = 0;

    public function mount()
    {
        $this->sale_date = now()->toDateString();
        $this->generateInvoiceNumber();

        $firstCustomer = Customer::first();
        if ($firstCustomer) {
            $this->customer_id = $firstCustomer->id;
        }

        $firstAccount = Account::first();
        if ($firstAccount) {
            $this->account_id = $firstAccount->id;
        }

        $this->addItem();
    }

    public function updatedSaleDate()
    {
        $this->generateInvoiceNumber();
    }

    public function generateInvoiceNumber()
    {
        $this->invoice_number = \App\Models\InvoiceSetting::getNextSalesInvoiceNumber($this->sale_date);
    }

    public function addItem()
    {
        $firstProd = Product::where('status', true)->where('current_stock', '>', 0)->orderBy('name')->first();
        $this->items[] = [
            'product_id' => $firstProd ? $firstProd->id : '',
            'quantity' => 1,
            'unit_price' => $firstProd ? $firstProd->sales_price : 0,
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
                    if ((float)$prod->current_stock <= 0) {
                        $this->items[$index]['product_id'] = '';
                        $msg = "'{$prod->name}' is out of stock and cannot be selected for sales.";
                        $this->addError("items.{$index}.product_id", $msg);
                        $this->dispatch('toast', message: $msg, type: 'danger', title: 'Out of Stock');
                    } else {
                        $this->items[$index]['unit_price'] = $prod->sales_price;
                        $this->items[$index]['vat_percent'] = $prod->tax_percent;
                    }
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

    public function saveSale(SalesService $salesService)
    {
        $this->resetErrorBag();

        $this->validate([
            'invoice_number' => 'required|unique:sales,invoice_number',
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_type' => 'required|in:Cash,Bank,Credit',
            'account_id' => 'required_if:payment_type,Cash,Bank',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $generalSetting = \App\Models\GeneralSetting::first();
        $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

        foreach ($this->items as $idx => $item) {
            $prod = Product::find($item['product_id']);
            if (!$prod || (float)$prod->current_stock <= 0) {
                $prodName = $prod ? $prod->name : 'Selected product';
                $msg = "'{$prodName}' is out of stock and cannot be sold.";
                $this->addError("items.{$idx}.product_id", $msg);
                session()->flash('error', $msg);
                $this->dispatch('toast', message: $msg, type: 'danger', title: 'Out of Stock');
                return;
            }

            $qty = (float)($item['quantity'] ?? 0);
            $avail = (float)$prod->current_stock;
            if (!$allowNegativeStock && $qty > $avail) {
                $msg = "Insufficient stock for '{$prod->name}'. Available stock is {$avail}, requested {$qty}.";
                $this->addError("items.{$idx}.quantity", $msg);
                session()->flash('error', $msg);
                $this->dispatch('toast', message: $msg, type: 'danger', title: 'Stock Insufficient');
                return;
            }
        }

        $header = [
            'invoice_number' => $this->invoice_number,
            'sale_date' => $this->sale_date,
            'customer_id' => $this->customer_id,
            'payment_type' => $this->payment_type,
            'account_id' => $this->account_id,
            'subtotal' => $this->subtotal,
            'discount_amount' => (float)$this->discount_amount,
            'vat_amount' => $this->vat_amount,
            'grand_total' => $this->grand_total,
            'notes' => $this->notes,
        ];

        try {
            $sale = $salesService->createSale($header, $this->items);
            session()->flash('success', "Sales Invoice #{$this->invoice_number} generated successfully.");
            $this->dispatch('toast', message: "Sales Invoice #{$this->invoice_number} generated successfully.", type: 'success', title: 'Invoice Issued');
            return redirect()->route('sales.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger', title: 'Sale Failed');
        }
    }

    public function render()
    {
        $generalSetting = \App\Models\GeneralSetting::first();
        $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

        $customers = Customer::select('id', 'name', 'company_name')->where('status', true)->orderBy('name')->get();
        $accounts = Account::select('id', 'name', 'type', 'current_balance')->where('status', true)->get();
        $products = Product::with('category')
            ->where('status', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('livewire.sales.create', [
            'customers' => $customers,
            'accounts' => $accounts,
            'products' => $products,
            'allowNegativeStock' => $allowNegativeStock,
        ])->layout('layouts.app', ['title' => 'Create Sales Invoice']);
    }
}
