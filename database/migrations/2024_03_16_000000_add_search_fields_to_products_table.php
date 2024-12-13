<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description');
                $table->string('variety')->nullable();
                $table->string('grade')->nullable();
                $table->string('growing_method')->nullable();
                $table->decimal('price', 10, 2);
                $table->string('price_unit');
                $table->decimal('minimum_order', 10, 2);
                $table->string('minimum_order_unit');
                $table->decimal('quantity_available', 10, 2);
                $table->string('quantity_unit');
                $table->string('country_of_origin');
                $table->string('region')->nullable();
                $table->date('harvest_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('storage_conditions')->nullable();
                $table->string('packaging_details')->nullable();
                $table->json('certifications')->nullable();
                $table->json('processing_level')->nullable();
                $table->json('payment_terms')->nullable();
                $table->json('delivery_terms')->nullable();
                $table->boolean('sample_available')->default(false);
                $table->json('available_months');
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'search_vector')) {
                $table->text('search_vector')->nullable();
            }
            if (!Schema::hasColumn('products', 'search_metadata')) {
                $table->json('search_metadata')->nullable();
            }
            if (!Schema::hasColumn('products', 'price_min')) {
                $table->decimal('price_min', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'price_max')) {
                $table->decimal('price_max', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'available_locations')) {
                $table->json('available_locations')->nullable();
            }
            if (!Schema::hasColumn('products', 'search_categories')) {
                $table->json('search_categories')->nullable();
            }
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