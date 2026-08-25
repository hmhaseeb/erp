<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // PCS, BOX, KG, METER, LITER, SET
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique()->index();
            $table->string('barcode')->nullable()->unique()->index();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('brand')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('purchase_price', 15, 2)->default(0.00);
            $table->decimal('sales_price', 15, 2)->default(0.00);
            $table->decimal('tax_percent', 5, 2)->default(0.00);
            $table->decimal('min_stock', 15, 2)->default(0.00);
            $table->decimal('opening_stock', 15, 2)->default(0.00);
            $table->decimal('current_stock', 15, 2)->default(0.00);
            $table->decimal('weighted_cost', 15, 2)->default(0.00);
            $table->string('warehouse')->default('Main Warehouse');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->date('date');
            $table->enum('movement_type', [
                'OPENING', 'PURCHASE', 'PURCHASE_RETURN', 
                'SALE', 'SALES_RETURN', 'ADJUSTMENT_IN', 'ADJUSTMENT_OUT'
            ]);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity_in', 15, 2)->default(0.00);
            $table->decimal('quantity_out', 15, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('balance_quantity', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_categories');
    }
};
