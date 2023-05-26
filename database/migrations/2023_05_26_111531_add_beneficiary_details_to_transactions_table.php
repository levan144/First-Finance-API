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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('beneficiary_country_code')->nullable();
            $table->string('beneficiary_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('intermediary_bank_name')->nullable();
            $table->string('intermediary_bank_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiary_country_code',
                'beneficiary_address',
                'bank_name',
                'bank_code',
                'intermediary_bank_name',
                'intermediary_bank_code',
            ]);
        });
    }
};
