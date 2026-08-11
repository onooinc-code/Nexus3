<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->text('api_key')->nullable()->after('base_url');
            $table->string('schema')->default('openai')->after('api_key');
            $table->string('response_method')->default('POST /v1/chat/completions')->after('schema');
            $table->integer('time_between_requests_ms')->default(0)->after('response_method');
            $table->json('models_list')->nullable()->after('time_between_requests_ms');
            $table->string('default_model')->nullable()->after('models_list');
            $table->string('status')->default('available')->after('default_model');
            $table->timestamp('rate_limit_reset_at')->nullable()->after('status');
            $table->json('custom_headers')->nullable()->after('rate_limit_reset_at');
            $table->integer('priority_order')->default(0)->after('custom_headers');
        });

        Schema::create('ai_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('ai_provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->string('level');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_usage_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('ai_provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->integer('requests_success')->default(0);
            $table->integer('requests_failed')->default(0);
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_latency_ms')->default(0);
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn([
                'api_key', 'schema', 'response_method', 'time_between_requests_ms',
                'models_list', 'default_model', 'status', 'rate_limit_reset_at',
                'custom_headers', 'priority_order',
            ]);
        });
        Schema::dropIfExists('ai_usage_stats');
        Schema::dropIfExists('ai_provider_logs');
    }
};
