<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('search_vector')->nullable();
            $table->json('search_metadata')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->json('available_locations')->nullable();
            $table->json('search_categories')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'search_vector',
                'search_metadata',
                'price_min',
                'price_max',
                'available_locations',
                'search_categories'
            ]);
        });
    }
}; 