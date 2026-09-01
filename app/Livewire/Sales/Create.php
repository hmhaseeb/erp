<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\GeneralSetting;
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

    // Quick Customer Modal
    public $isCustomerModalOpen = false;
    public $cust_code, $cust_name, $cust_company_name, $cust_contact_person;
    public $cust_mobile, $cust_email, $cust_address, $cust_trn_number;
    public $cust_opening_balance = 0, $cust_credit_limit = 0, $cust_payment_terms, $cust_notes;

    public function mount()
    {
        $this->sale_date = now()->toDateString();
        $this->generateInvoiceNumber();

        $firstCustomer = Customer::where('status', true)->first();
        if ($firstCustomer) {
            $this->customer_id = $firstCustomer->id;
        }

        $firstAccount = Account::where('status', true)->first();
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
        $selectedIds = array_filter(array_column($this->items, 'product_id'));
        $generalSetting = GeneralSetting::first();
        $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

        $firstProdQuery = Product::where('status', true)
            ->whereNotIn('id', $selectedIds)
            ->orderBy('name');

        if (!$allowNegativeStock) {
            $firstProdQuery->where('current_stock', '>', 0);
        }

        $firstProd = $firstProdQuery->first();

        if (!$firstProd && count($selectedIds) > 0) {
            $this->dispatch('toast', message: 'All available in-stock products have already been added to this sales invoice.', type: 'warning', title: 'Cannot Add More Products');
            return;
        }

        $availStock = $firstProd ? (float)$firstProd->current_stock : 0;
        $initialQty = 1;
        if ($availStock > 0 && $availStock < 1) {
            $initialQty = $availStock;
        }

        $this->items[] = [
            'product_id' => $firstProd ? $firstProd->id : '',
            'quantity' => $initialQty,
            'unit_price' => $firstProd ? (float)$firstProd->sales_price : 0,
            'discount_amount' => 0,
            'vat_percent' => $firstProd ? (float)($firstProd->tax_percent ?? 5) : 5,
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
        $index = (int)$parts[0];
        $field = $parts[1] ?? null;

        if ($field === 'product_id') {
            $duplicate = false;
            foreach ($this->items as $i => $item) {
                if ($i !== $index && !empty($item['product_id']) && (string)$item['product_id'] === (string)$value) {
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
                $this->dispatch('toast', message: "'{$dupName}' is already added to this invoice. Duplicate products are not allowed.", type: 'warning', title: 'Duplicate Item');
                return;
            }

            $prod = Product::find($value);
            if ($prod) {
                $generalSetting = GeneralSetting::first();
                $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;
                $availStock = (float)$prod->current_stock;

                if (!$allowNegativeStock && $availStock <= 0) {
                    $this->items[$index]['product_id'] = '';
                    $this->items[$index]['unit_price'] = 0;
                    $this->items[$index]['vat_percent'] = 5;
                    $this->calculateTotals();
                    $this->dispatch('toast', message: "'{$prod->name}' is out of stock (0 available) and cannot be added to a sales invoice.", type: 'danger', title: 'Out of Stock');
                    return;
                }

                $this->items[$index]['unit_price'] = (float)$prod->sales_price;
                $this->items[$index]['vat_percent'] = (float)($prod->tax_percent ?? 5);

                // Auto adjust row quantity if existing quantity exceeds available stock
                $currentQty = (float)($this->items[$index]['quantity'] ?? 1);
                if ($currentQty <= 0) {
                    $currentQty = $availStock > 0 && $availStock < 1 ? $availStock : 1;
                    $this->items[$index]['quantity'] = $currentQty;
                }

                if (!$allowNegativeStock && $currentQty > $availStock) {
                    $this->items[$index]['quantity'] = $availStock;
                    $this->dispatch('toast', message: "Quantity adjusted to {$availStock} based on available stock for '{$prod->name}'.", type: 'info', title: 'Quantity Adjusted');
                }

                $this->resetErrorBag("items.{$index}.quantity");
                $this->resetErrorBag("items.{$index}.product_id");
            }
        }

        if ($field === 'quantity') {
            $qty = (float)$value;
            $prodId = $this->items[$index]['product_id'] ?? null;

            if ($value === '' || $value === null || $qty <= 0) {
                $msg = "Quantity must be a positive number greater than 0.";
                $this->addError("items.{$index}.quantity", $msg);
                $this->dispatch('toast', message: $msg, type: 'warning', title: 'Invalid Quantity');
                $this->items[$index]['quantity'] = 1;
            } elseif ($prodId) {
                $generalSetting = GeneralSetting::first();
                $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

                if (!$allowNegativeStock) {
                    $prod = Product::find($prodId);
                    if ($prod) {
                        $availStock = (float)$prod->current_stock;

                        // Calculate total requested quantity of this product across all lines
                        $totalReq = 0;
                        foreach ($this->items as $i => $it) {
                            if (($it['product_id'] ?? null) == $prodId) {
                                $totalReq += ($i === $index ? $qty : (float)($it['quantity'] ?? 0));
                            }
                        }

                        if ($totalReq > $availStock) {
                            $otherItemsQty = $totalReq - $qty;
                            $maxAllowedForThisRow = max(0, $availStock - $otherItemsQty);
                            $this->items[$index]['quantity'] = $maxAllowedForThisRow;

                            $msg = "Cannot sell {$qty}. Only {$availStock} available in stock for '{$prod->name}'. Quantity clamped to {$maxAllowedForThisRow}.";
                            $this->addError("items.{$index}.quantity", $msg);
                            $this->dispatch('toast', message: $msg, type: 'warning', title: 'Stock Limit Exceeded');
                        } else {
                            $this->resetErrorBag("items.{$index}.quantity");
                        }
                    }
                } else {
                    $this->resetErrorBag("items.{$index}.quantity");
                }
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

    public function saveSale(SalesService $salesService)
    {
        $this->validate([
            'invoice_number' => 'required|string',
            'sale_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:Cash,Bank,Credit',
            'account_id' => 'required_if:payment_type,Cash,Bank|nullable|exists:accounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'numeric|min:0',
        ]);

        // Validate duplicates
        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            $msg = 'Duplicate products detected in invoice items. Each product may only appear once.';
            $this->dispatch('toast', message: $msg, type: 'danger', title: 'Validation Error');
            return;
        }

        $generalSetting = GeneralSetting::first();
        $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

        // Aggregate quantities per product for strict stock check
        $productQuantities = [];
        foreach ($this->items as $idx => $item) {
            $pid = $item['product_id'];
            $qty = (float)($item['quantity'] ?? 0);
            if ($qty <= 0) {
                $msg = "Quantity for item #" . ($idx + 1) . " must be a positive number greater than 0.";
                $this->addError("items.{$idx}.quantity", $msg);
                $this->dispatch('toast', message: $msg, type: 'danger', title: 'Invalid Quantity');
                return;
            }
            $productQuantities[$pid] = ($productQuantities[$pid] ?? 0) + $qty;
        }

        if (!$allowNegativeStock) {
            $products = Product::whereIn('id', array_keys($productQuantities))->get()->keyBy('id');
            foreach ($this->items as $idx => $item) {
                $pid = $item['product_id'];
                $prod = $products[$pid] ?? null;
                if (!$prod) {
                    $msg = "Selected product is invalid or no longer exists.";
                    $this->addError("items.{$idx}.product_id", $msg);
                    $this->dispatch('toast', message: $msg, type: 'danger', title: 'Invalid Product');
                    return;
                }

                $avail = (float)$prod->current_stock;
                if ($avail <= 0) {
                    $msg = "'{$prod->name}' is out of stock (0 available) and cannot be sold.";
                    $this->addError("items.{$idx}.product_id", $msg);
                    $this->dispatch('toast', message: $msg, type: 'danger', title: 'Out of Stock');
                    return;
                }

                $totalReq = $productQuantities[$pid];
                if ($totalReq > $avail) {
                    $msg = "Insufficient stock for '{$prod->name}'. Available stock is {$avail}, requested {$totalReq}.";
                    $this->addError("items.{$idx}.quantity", $msg);
                    $this->dispatch('toast', message: $msg, type: 'danger', title: 'Stock Insufficient');
                    return;
                }
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
            session()->flash('success', "Sales Invoice #{$sale->invoice_number} generated successfully.");
            $this->dispatch('toast', message: "Sales Invoice #{$sale->invoice_number} generated successfully.", type: 'success', title: 'Invoice Issued');
            return redirect()->route('sales.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger', title: 'Sale Failed');
        }
    }

    // ==========================================
    // Quick Add Customer Methods
    // ==========================================
    public function openCustomerModal()
    {
        $this->resetValidation();
        $this->reset([
            'cust_name', 'cust_company_name', 'cust_contact_person',
            'cust_mobile', 'cust_email', 'cust_address', 'cust_trn_number',
            'cust_opening_balance', 'cust_credit_limit', 'cust_payment_terms', 'cust_notes'
        ]);
        $setting = GeneralSetting::first();
        $prefix = $setting ? $setting->customer_prefix : 'CUST-';
        $maxId = Customer::max('id') + 1;
        $this->cust_code = $prefix . str_pad((string)$maxId, 5, '0', STR_PAD_LEFT);
        $this->isCustomerModalOpen = true;
    }

    public function closeCustomerModal()
    {
        $this->isCustomerModalOpen = false;
        $this->resetValidation();
    }

    public function saveNewCustomer()
    {
        $this->validate([
            'cust_code' => 'required|string|max:50|unique:customers,customer_code',
            'cust_name' => 'required|string|max:255',
            'cust_company_name' => 'nullable|string|max:255',
            'cust_mobile' => 'nullable|string|max:50',
            'cust_email' => 'nullable|email|max:255',
            'cust_opening_balance' => 'numeric|min:0',
            'cust_credit_limit' => 'numeric|min:0',
        ]);

        $customer = Customer::create([
            'customer_code' => $this->cust_code,
            'name' => $this->cust_name,
            'company_name' => $this->cust_company_name,
            'contact_person' => $this->cust_contact_person,
            'mobile' => $this->cust_mobile,
            'email' => $this->cust_email,
            'address' => $this->cust_address,
            'trn_number' => $this->cust_trn_number,
            'opening_balance' => $this->cust_opening_balance ?: 0,
            'current_balance' => $this->cust_opening_balance ?: 0,
            'credit_limit' => $this->cust_credit_limit ?: 0,
            'payment_terms' => $this->cust_payment_terms,
            'notes' => $this->cust_notes,
            'status' => true,
        ]);

        $this->customer_id = $customer->id;
        $this->isCustomerModalOpen = false;
        $this->dispatch('toast', message: "Customer '{$customer->name}' registered and selected successfully.", type: 'success', title: 'Customer Added');
    }

    public function render()
    {
        $generalSetting = GeneralSetting::first();
        $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

        $customers = Customer::select('id', 'name', 'company_name', 'current_balance')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $accounts = Account::select('id', 'name', 'type', 'current_balance')
            ->where('status', true)
            ->get();

        $products = Product::select('id', 'name', 'category_id', 'product_code', 'current_stock', 'sales_price', 'purchase_price', 'tax_percent')
            ->with('category:id,name')
            ->where('status', true)
            ->where(function ($q) use ($allowNegativeStock) {
                if (!$allowNegativeStock) {
                    $q->where('current_stock', '>', 0);
                }
            })
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
