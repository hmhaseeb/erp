<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('My Business ERP');
            $table->string('legal_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('United Arab Emirates');
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('trn_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('currency')->default('AED');
            $table->string('currency_symbol')->default('AED');
            $table->decimal('default_vat_percent', 5, 2)->default(5.00);
            $table->string('financial_year_start')->default('01-01');
            $table->string('main_logo')->nullable();
            $table->string('invoice_logo')->nullable();
            $table->string('report_logo')->nullable();
            $table->string('login_logo')->nullable();
            $table->string('favicon')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_prefix')->default('INV-');
            $table->integer('starting_number')->default(1);
            $table->string('purchase_prefix')->default('PUR-');
            $table->string('sales_return_prefix')->default('SR-');
            $table->string('purchase_return_prefix')->default('PR-');
            $table->string('customer_payment_prefix')->default('REC-');
            $table->string('supplier_payment_prefix')->default('PAY-');
            $table->text('invoice_footer')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('paper_size')->default('A4');
            $table->boolean('show_vat')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->timestamps();
        });

        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_zone')->default('Asia/Dubai');
            $table->integer('decimal_places')->default(2);
            $table->unsignedBigInteger('default_cash_account_id')->nullable();
            $table->unsignedBigInteger('default_bank_account_id')->nullable();
            $table->string('product_prefix')->default('PROD-');
            $table->string('supplier_prefix')->default('SUP-');
            $table->string('customer_prefix')->default('CUST-');
            $table->boolean('allow_negative_stock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('company_settings');
    }
};
