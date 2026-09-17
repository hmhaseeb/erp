<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'sales_person')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sales_person')->nullable()->after('customer_id');
            });
        }

        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'sales_person')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('sales_person')->nullable()->after('supplier_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'sales_person')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('sales_person');
            });
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'sales_person')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('sales_person');
            });
        }
    }
};
