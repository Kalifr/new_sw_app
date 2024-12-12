<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Support ticket categories
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Support tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('category_id')->constrained('support_categories');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('subject');
            $table->text('description');
            $table->string('status');
            $table->string('priority');
            $table->json('metadata')->nullable();
            $table->timestamp('last_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_number', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // Support ticket responses
        Schema::create('support_ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('response_type')->default('reply'); // reply, note, status_change
            $table->boolean('is_internal')->default(false);
            $table->string('email_message_id')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Support ticket attachments
        Schema::create('support_ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('response_id')->nullable()->constrained('support_ticket_responses')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();
        });

        // Support ticket feedback
        Schema::create('support_ticket_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating');
            $table->text('comments')->nullable();
            $table->json('survey_responses')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id']);
        });

        // FAQ entries generated from support tickets
        Schema::create('support_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('support_categories');
            $table->string('question');
            $table->text('answer');
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->json('related_ticket_ids')->nullable();
            $table->timestamps();
        });

        // Support ticket metrics
        Schema::create('support_ticket_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type');
            $table->string('category')->nullable();
            $table->decimal('value', 10, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['date', 'metric_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_metrics');
        Schema::dropIfExists('support_faqs');
        Schema::dropIfExists('support_ticket_feedback');
        Schema::dropIfExists('support_ticket_attachments');
        Schema::dropIfExists('support_ticket_responses');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_categories');
    }
}; 