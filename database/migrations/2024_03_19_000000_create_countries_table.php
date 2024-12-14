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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('2_letter_isocode', 2)->unique();
            $table->string('3_letter_isocode', 3)->unique();
            $table->string('country_name');
            $table->string('slug')->unique();
            $table->string('region');
            $table->string('language');
            $table->string('native_language_name');
            $table->string('language_code', 5);
            $table->string('currency_code', 3);
            $table->string('currency_name');
            $table->decimal('currency_to_usd_exchange_rate', 10, 4);
            $table->string('capital_city');
            $table->string('second_city')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
}; 