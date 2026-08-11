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
        Schema::create('hermes_messages', function (Blueprint $table) {
            $table->id();
            $table->string('hermes_session_id');
            $table->string('role')->default('user');
            $table->longText('content')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();

            $table->foreign('hermes_session_id')->references('id')->on('hermes_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hermes_messages');
    }
};
