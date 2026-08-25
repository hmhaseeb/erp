<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('brand');
            $table->index('status');
            $table->index(['category_id', 'status']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('name');
            $table->index('mobile');
            $table->index('status');
            $table->index(['status', 'current_balance']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name');
            $table->index('mobile');
            $table->index('status');
            $table->index(['status', 'current_balance']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_date');
            $table->index('status');
            $table->index('payment_type');
            $table->index(['customer_id', 'sale_date']);
            $table->index(['status', 'sale_date']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('purchase_date');
            $table->index('status');
            $table->index('payment_type');
            $table->index(['supplier_id', 'purchase_date']);
            $table->index(['status', 'purchase_date']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->index('return_date');
            $table->index('status');
            $table->index(['customer_id', 'return_date']);
        });

        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->index('return_date');
            $table->index('status');
            $table->index(['supplier_id', 'return_date']);
        });

        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->index('payment_date');
            $table->index(['customer_id', 'payment_date']);
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->index('payment_date');
            $table->index(['supplier_id', 'payment_date']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->index('date');
            $table->index(['income_category_id', 'date']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('date');
            $table->index(['expense_category_id', 'date']);
        });

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->index('transaction_type');
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('movement_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        // Drop added indexes if needed
    }
};
