<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        // Add inspector-specific fields to user profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique();
            $table->json('inspection_regions')->nullable();
            $table->json('certifications')->nullable();
            $table->boolean('is_active_inspector')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'inspection_regions',
                'certifications',
                'is_active_inspector'
            ]);
        });

        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
}; 