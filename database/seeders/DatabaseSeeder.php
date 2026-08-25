<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\ExpenseCategory;
use App\Models\GeneralSetting;
use App\Models\IncomeCategory;
use App\Models\InvoiceSetting;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Single Administrator User
        User::firstOrCreate(
            ['email' => 'admin@erp.com'],
            [
                'name' => 'ERP Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // 2. Default Company Settings
        CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Apex General Trading LLC',
                'legal_name' => 'Apex General Trading LLC',
                'address' => 'Business Bay, Tower 1, Suite 402',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates',
                'phone' => '+971 4 123 4567',
                'mobile' => '+971 50 987 6543',
                'email' => 'info@apexerp.com',
                'website' => 'www.apexerp.com',
                'trn_number' => '100293847500003',
                'currency' => 'AED',
                'currency_symbol' => 'AED',
                'default_vat_percent' => 5.00,
            ]
        );

        // 3. Invoice Settings
        InvoiceSetting::firstOrCreate(
            ['id' => 1],
            [
                'invoice_prefix' => 'INV-',
                'starting_number' => 1,
                'purchase_prefix' => 'PUR-',
                'sales_return_prefix' => 'SR-',
                'purchase_return_prefix' => 'PR-',
                'customer_payment_prefix' => 'REC-',
                'supplier_payment_prefix' => 'PAY-',
                'invoice_footer' => 'Thank you for your business!',
                'terms_conditions' => 'Payment due within terms. Goods once sold are returnable within 7 days in original condition.',
                'bank_details' => "Bank Name: Emirates NBD\nAccount Name: Apex General Trading LLC\nIBAN: AE000330000012345678901",
                'paper_size' => 'A4',
                'show_vat' => true,
                'show_discount' => true,
            ]
        );

        // 4. Accounts (Cash & Bank)
        $cashAccount = Account::firstOrCreate(
            ['name' => 'Main Cash Account'],
            [
                'type' => 'Cash',
                'opening_balance' => 10000.00,
                'opening_balance_date' => now()->toDateString(),
                'current_balance' => 10000.00,
                'status' => true,
                'notes' => 'Primary cash drawer',
            ]
        );

        $bankAccount = Account::firstOrCreate(
            ['name' => 'Emirates NBD Bank'],
            [
                'type' => 'Bank',
                'bank_name' => 'Emirates NBD',
                'account_number' => '12345678901',
                'opening_balance' => 50000.00,
                'opening_balance_date' => now()->toDateString(),
                'current_balance' => 50000.00,
                'status' => true,
                'notes' => 'Primary operating bank account',
            ]
        );

        // 5. General Settings
        GeneralSetting::firstOrCreate(
            ['id' => 1],
            [
                'date_format' => 'Y-m-d',
                'time_zone' => 'Asia/Dubai',
                'decimal_places' => 2,
                'default_cash_account_id' => $cashAccount->id,
                'default_bank_account_id' => $bankAccount->id,
                'product_prefix' => 'PROD-',
                'supplier_prefix' => 'SUP-',
                'customer_prefix' => 'CUST-',
                'allow_negative_stock' => false,
            ]
        );

        // 6. Units
        $units = ['PCS', 'BOX', 'KG', 'METER', 'LITER', 'SET'];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u], ['code' => $u]);
        }

        // 7. Product Categories
        $categories = [
            'Electronics' => 'ELEC',
            'Office Supplies' => 'OFFC',
            'Hardware & Tools' => 'HARD',
            'Raw Materials' => 'RAW',
        ];
        foreach ($categories as $catName => $code) {
            ProductCategory::firstOrCreate(['name' => $catName], ['code' => $code, 'status' => true]);
        }

        // 8. Income & Expense Categories
        $incomeCats = ['Service Income', 'Commission', 'Rental Income', 'Other Income'];
        foreach ($incomeCats as $ic) {
            IncomeCategory::firstOrCreate(['name' => $ic], ['status' => true]);
        }

        $expenseCats = [
            'Rent', 'Electricity', 'Internet', 'Salary', 
            'Transport', 'Fuel', 'Office Supplies', 'Maintenance', 
            'Marketing', 'Other Expense'
        ];
        foreach ($expenseCats as $ec) {
            ExpenseCategory::firstOrCreate(['name' => $ec], ['status' => true]);
        }
    }
}
