<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('price_unit');
            $table->decimal('quantity', 10, 2);
            $table->string('quantity_unit');
            $table->text('specifications')->nullable();
            $table->text('packaging_details')->nullable();
            $table->text('shipping_details')->nullable();
            $table->text('quality_certifications')->nullable();
            $table->date('delivery_date');
            $table->string('payment_terms');
            $table->text('additional_notes')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, rejected, expired
            $table->date('valid_until');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_quotes');
    }
}; 