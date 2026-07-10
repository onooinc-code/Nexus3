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
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('is_active');
            $table->json('tags')->nullable()->after('notes');
            $table->integer('sort_order')->default(0)->after('tags');
            $table->boolean('is_favorite')->default(false)->after('sort_order');
            $table->string('auto_sync_interval', 20)->nullable()->after('is_favorite')->comment('never/6h/12h/24h/weekly');
            $table->integer('circuit_breaker_threshold')->nullable()->after('auto_sync_interval');
            $table->integer('request_timeout_ms')->nullable()->after('circuit_breaker_threshold');
            $table->integer('max_retries')->nullable()->after('request_timeout_ms');
            $table->decimal('monthly_budget_cap', 12, 4)->nullable()->after('max_retries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn([
                'notes',
                'tags',
                'sort_order',
                'is_favorite',
                'auto_sync_interval',
                'circuit_breaker_threshold',
                'request_timeout_ms',
                'max_retries',
                'monthly_budget_cap'
            ]);
        });
    }
};
