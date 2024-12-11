<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('variety')->nullable();
            $table->string('grade')->nullable();
            $table->string('growing_method')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('price_unit'); // per tonne, per kg, etc.
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
            $table->json('certifications')->nullable(); // ['organic', 'fairtrade', etc.]
            $table->json('processing_level')->nullable(); // ['raw', 'semi-processed', 'processed']
            $table->json('payment_terms')->nullable();
            $table->json('delivery_terms')->nullable();
            $table->boolean('sample_available')->default(false);
            $table->json('available_months'); // Array of months when product is available
            $table->string('status')->default('draft'); // draft, published, sold, expired
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}; 