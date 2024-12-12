<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->after('id');
            $table->string('meta_title')->nullable()->after('status');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('canonical_url')->nullable()->after('meta_description');
            
            // Add indexes for faster lookups
            $table->index('country_of_origin');
            $table->index(['status', 'country_of_origin']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['slug', 'meta_title', 'meta_description', 'canonical_url']);
            $table->dropIndex(['country_of_origin']);
            $table->dropIndex(['status', 'country_of_origin']);
        });
    }
}; 