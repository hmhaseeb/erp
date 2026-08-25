<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->index();
            $table->date('sale_date');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->enum('payment_type', ['Cash', 'Bank', 'Credit'])->default('Credit');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('due_amount', 15, 2)->default(0.00);
            $table->decimal('cogs_total', 15, 2)->default(0.00); // Total cost of goods sold for P&L
            $table->enum('status', ['Draft', 'Confirmed', 'Cancelled'])->default('Confirmed');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00); // Weighted average cost at sale time
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('vat_percent', 5, 2)->default(0.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('line_total', 15, 2)->default(0.00);
            $table->decimal('cogs_amount', 15, 2)->default(0.00); // qty * unit_cost
            $table->timestamps();
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique()->index();
            $table->date('return_date');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->decimal('cogs_total', 15, 2)->default(0.00);
            $table->text('return_reason')->nullable();
            $table->enum('status', ['Confirmed', 'Cancelled'])->default('Confirmed');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->onDelete('cascade');
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('vat_percent', 5, 2)->default(0.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('line_total', 15, 2)->default(0.00);
            $table->decimal('cogs_amount', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
