<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('income_category_id')->constrained('income_categories')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->string('reference_number')->nullable();
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->string('reference_number')->nullable();
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('income_categories');
    }
};
