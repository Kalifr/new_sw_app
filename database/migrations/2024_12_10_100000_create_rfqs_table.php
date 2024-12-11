<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->string('quantity_unit');
            $table->text('specifications')->nullable();
            $table->text('packaging_requirements')->nullable();
            $table->text('shipping_requirements')->nullable();
            $table->text('quality_requirements')->nullable();
            $table->text('certification_requirements')->nullable();
            $table->date('target_delivery_date')->nullable();
            $table->string('target_price_range')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('delivery_location');
            $table->string('status')->default('open'); // open, closed, expired
            $table->date('valid_until');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
}; 