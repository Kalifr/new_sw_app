<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daily metrics aggregation
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type');
            $table->string('dimension')->nullable();
            $table->decimal('value', 15, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['date', 'metric_type']);
            $table->index(['metric_type', 'dimension']);
        });

        // User activity logs
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'activity_type']);
            $table->index(['entity_type', 'entity_id']);
        });

        // Search analytics
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->integer('results_count');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('category')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamps();

            $table->index('query');
            $table->index('category');
        });

        // User sessions
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });

        // Platform metrics snapshots (for historical tracking)
        Schema::create('platform_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_users');
            $table->integer('total_products');
            $table->integer('total_orders');
            $table->integer('total_rfqs');
            $table->decimal('total_order_value', 15, 2);
            $table->decimal('average_order_value', 15, 2);
            $table->json('category_distribution')->nullable();
            $table->json('location_distribution')->nullable();
            $table->json('additional_metrics')->nullable();
            $table->timestamps();

            $table->unique('date');
        });

        // User metrics snapshots
        Schema::create('user_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('metric_type');
            $table->decimal('value', 15, 2);
            $table->date('date');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'metric_type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_metrics');
        Schema::dropIfExists('platform_snapshots');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('search_analytics');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('daily_metrics');
    }
}; 