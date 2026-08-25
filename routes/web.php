<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Accounts;
use App\Livewire\Auth\Login;
use App\Livewire\Customers;
use App\Livewire\Dashboard;
use App\Livewire\Expenses;
use App\Livewire\Income;
use App\Livewire\Payments;
use App\Livewire\Products;
use App\Livewire\Purchases;
use App\Livewire\Reports;
use App\Livewire\Sales;
use App\Livewire\Settings;
use App\Livewire\Suppliers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class);

    // Accounts
    Route::get('/accounts', Accounts\Index::class)->name('accounts.index');
    Route::get('/accounts/transactions', Accounts\Transactions::class)->name('accounts.transactions');
    Route::get('/accounts/ledger', Accounts\Ledger::class)->name('accounts.ledger');

    // Products
    Route::get('/products', Products\Index::class)->name('products.index');
    Route::get('/products/categories', Products\Categories::class)->name('products.categories');
    Route::get('/products/units', Products\Units::class)->name('products.units');
    Route::get('/products/stock', Products\Stock::class)->name('products.stock');

    // Contacts
    Route::get('/suppliers', Suppliers\Index::class)->name('suppliers.index');
    Route::get('/customers', Customers\Index::class)->name('customers.index');

    // Purchases
    Route::get('/purchases', Purchases\Index::class)->name('purchases.index');
    Route::get('/purchases/create', Purchases\Create::class)->name('purchases.create');
    Route::get('/purchases/returns', Purchases\Returns::class)->name('purchases.returns');

    // Sales
    Route::get('/sales', Sales\Index::class)->name('sales.index');
    Route::get('/sales/create', Sales\Create::class)->name('sales.create');
    Route::get('/sales/returns', Sales\Returns::class)->name('sales.returns');
    Route::get('/sales/pdf/{id}', [InvoicePdfController::class, 'downloadPdf'])->name('sales.pdf');

    // Payments
    Route::get('/payments/customer', Payments\CustomerPayments::class)->name('payments.customer');
    Route::get('/payments/supplier', Payments\SupplierPayments::class)->name('payments.supplier');

    // Income
    Route::get('/income/categories', Income\Categories::class)->name('income.categories');
    Route::get('/income', Income\Index::class)->name('income.index');

    // Expenses
    Route::get('/expenses/categories', Expenses\Categories::class)->name('expenses.categories');
    Route::get('/expenses', Expenses\Index::class)->name('expenses.index');

    // Reports
    Route::get('/reports/daily', Reports\DailyReport::class)->name('reports.daily');
    Route::get('/reports/sales', Reports\SalesReport::class)->name('reports.sales');
    Route::get('/reports/purchases', Reports\PurchaseReport::class)->name('reports.purchases');
    Route::get('/reports/stock', Reports\StockReport::class)->name('reports.stock');
    Route::get('/reports/cashbook', Reports\CashBook::class)->name('reports.cashbook');
    Route::get('/reports/bankbook', Reports\BankBook::class)->name('reports.bankbook');
    Route::get('/reports/receivables', Reports\ReceivablesReport::class)->name('reports.receivables');
    Route::get('/reports/payables', Reports\PayablesReport::class)->name('reports.payables');
    Route::get('/reports/profit-loss', Reports\ProfitLoss::class)->name('reports.profit-loss');

    // Settings
    Route::get('/settings/company', Settings\CompanySettings::class)->name('settings.company');
    Route::get('/settings/invoice', Settings\InvoiceSettings::class)->name('settings.invoice');
    Route::get('/settings/logos', Settings\LogoSettings::class)->name('settings.logos');
    Route::get('/settings/general', Settings\GeneralSettings::class)->name('settings.general');
});
