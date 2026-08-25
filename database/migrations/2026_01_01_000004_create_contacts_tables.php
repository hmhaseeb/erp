<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique()->index();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('trn_number')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00); // Payable balance
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->date('date');
            $table->string('transaction_type'); // PURCHASE, PAYMENT, RETURN, ADJUSTMENT
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0.00); // Amount paid / reduced payable
            $table->decimal('credit', 15, 2)->default(0.00); // Amount purchased / increased payable
            $table->decimal('balance', 15, 2)->default(0.00); // Running payable balance
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'date']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique()->index();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('trn_number')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00); // Receivable balance
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('date');
            $table->string('transaction_type'); // SALE, PAYMENT, RETURN, ADJUSTMENT
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0.00); // Amount billed / increased receivable
            $table->decimal('credit', 15, 2)->default(0.00); // Amount paid / reduced receivable
            $table->decimal('balance', 15, 2)->default(0.00); // Running receivable balance
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_transactions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('supplier_transactions');
        Schema::dropIfExists('suppliers');
    }
};
