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
        Schema::create('hermes_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('source')->nullable();
            $table->string('model')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason')->nullable();
            $table->integer('message_count')->default(0);
            $table->integer('tool_call_count')->default(0);
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->text('preview')->nullable();
            $table->timestamp('last_active')->nullable();
            $table->boolean('pinned')->default(false);
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hermes_sessions');
    }
};
