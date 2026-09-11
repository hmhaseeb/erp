<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: Ensure `country` column in company_settings has a default value
     * and is NOT NULL — matching migration intent. Also back-fills any null rows.
     */
    public function up(): void
    {
        // Back-fill any existing null values before altering the column
        DB::table('company_settings')
            ->whereNull('country')
            ->orWhere('country', '')
            ->update(['country' => 'United Arab Emirates']);

        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('country')->default('United Arab Emirates')->change();
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('country')->nullable()->default(null)->change();
        });
    }
};
