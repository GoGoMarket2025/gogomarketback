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
        Schema::table('shops', function (Blueprint $table) {
            // Организация
            $table->string('organization_type')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('organization_oked', 10)->nullable(); // окэд обычно до 5-6 цифр, запас сделал 10

            // Банковские данные
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_mfo_code', 10)->nullable();

            // Паспортные данные
            $table->string('passport_serial', 5)->nullable(); // например, AB
            $table->string('passport_number', 15)->nullable();
            $table->string('passport_issue_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'organization_type',
                'organization_name',
                'organization_oked',
                'bank_account_number',
                'bank_name',
                'bank_mfo_code',
                'passport_serial',
                'passport_number',
                'passport_issue_name',
            ]);
        });
    }
};
