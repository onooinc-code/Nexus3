<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update usage_logs table
        Schema::table('usage_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('usage_logs', 'api_key_id')) {
                $table->uuid('api_key_id')->nullable()->after('model_id')->index();
            }
        });

        // 2. Update intent_routing table
        if (Schema::hasTable('intent_routing')) {
            Schema::table('intent_routing', function (Blueprint $table) {
                if (! Schema::hasColumn('intent_routing', 'fallback_chain')) {
                    $table->json('fallback_chain')->nullable()->after('fallback_model_id');
                }
            });
        }

        // 3. Create ai_ab_experiments table
        if (! Schema::hasTable('ai_ab_experiments')) {
            Schema::create('ai_ab_experiments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('intent_name')->index();
                $table->uuid('model_a_id')->nullable();
                $table->uuid('model_b_id')->nullable();
                $table->integer('weight_a')->default(50);
                $table->integer('weight_b')->default(50);
                $table->string('goal_metric')->default('lowest_cost');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('model_a_id')->references('id')->on('ai_models')->onDelete('cascade');
                $table->foreign('model_b_id')->references('id')->on('ai_models')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_ab_experiments');

        if (Schema::hasTable('intent_routing')) {
            Schema::table('intent_routing', function (Blueprint $table) {
                if (Schema::hasColumn('intent_routing', 'fallback_chain')) {
                    $table->dropColumn('fallback_chain');
                }
            });
        }

        Schema::table('usage_logs', function (Blueprint $table) {
            if (Schema::hasColumn('usage_logs', 'api_key_id')) {
                $table->dropColumn('api_key_id');
            }
        });
    }
};
