<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Cash', 'Bank', 'Other'])->default('Cash');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->date('opening_balance_date')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('transaction_type'); // Cash In, Cash Out, Deposit, Withdrawal, Transfer, Sale, Purchase, Expense, Income, Customer Payment, Supplier Payment
            $table->string('reference_type')->nullable(); // Model class or type
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0.00); // Money received/added to account
            $table->decimal('credit', 15, 2)->default(0.00); // Money paid/withdrawn from account
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('accounts');
    }
};
