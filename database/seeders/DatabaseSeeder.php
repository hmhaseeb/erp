<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
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

        // 2. Units of Measure
        $units = ['PCS', 'BOX', 'KG', 'METER', 'LITER', 'SET'];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u], ['code' => $u]);
        }
        
        // 3. Income Categories
        $incomeCats = ['Service Income', 'Commission', 'Rental Income', 'Other Income'];
        foreach ($incomeCats as $ic) {
            IncomeCategory::firstOrCreate(['name' => $ic], ['status' => true]);
        }

        // 4. Expense Categories
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
