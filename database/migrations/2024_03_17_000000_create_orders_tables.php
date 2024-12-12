<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('rfq_id')->constrained();
            $table->foreignId('quote_id')->constrained('rfq_quotes');
            $table->string('order_number')->unique();
            $table->string('status');
            $table->string('currency')->default('USD');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('shipping_method');
            $table->json('shipping_details');
            $table->string('inspection_status')->default('pending');
            $table->timestamp('payment_due_date');
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Order documents table
        Schema::create('order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('type'); // proforma_invoice, purchase_order, inspection_report, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Order status history
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('status');
            $table->string('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Payment records
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency');
            $table->string('payment_method');
            $table->string('status');
            $table->string('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Inspection records
        Schema::create('inspection_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspector_id')->constrained('users');
            $table->string('status');
            $table->text('findings');
            $table->json('checklist_results');
            $table->json('photos')->nullable();
            $table->string('location');
            $table->timestamp('inspection_date');
            $table->timestamps();
        });

        // Digital signatures
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('document_id')->constrained('order_documents');
            $table->string('signature_hash');
            $table->string('certificate_hash')->nullable();
            $table->json('metadata');
            $table->timestamp('signed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
        Schema::dropIfExists('inspection_records');
        Schema::dropIfExists('payment_records');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_documents');
        Schema::dropIfExists('orders');
    }
}; 