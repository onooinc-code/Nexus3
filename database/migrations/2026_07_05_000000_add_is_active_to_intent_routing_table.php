<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('intent_routing', 'is_active')) {
            Schema::table('intent_routing', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('fallback_model_id');
                $table->index(['is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('intent_routing', 'is_active')) {
            Schema::table('intent_routing', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            });
        }
    }
};
