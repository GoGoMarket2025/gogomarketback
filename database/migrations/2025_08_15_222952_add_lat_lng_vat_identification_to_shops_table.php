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
            // Гео-координаты: decimal (широта -90..90, долгота -180..180)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();

            // VAT процент: 0 или 12
            $table->unsignedTinyInteger('vat_percent')->default(0);
            // Если у вас MySQL 8+/PostgreSQL, можно зафиксировать значения чек-ограничением:
            // $table->unsignedTinyInteger('vat_percent')->default(0)->check('vat_percent in (0,12)');

            // ИНН/ПИНФЛ, максимум 14 символов
            $table->string('identification_number', 14)->nullable()->index();
            // Если нужен запрет дублей:
            // $table->string('identification_number', 14)->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'vat_percent', 'identification_number']);
        });
    }
};
