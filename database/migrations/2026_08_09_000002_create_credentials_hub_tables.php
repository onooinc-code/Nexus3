<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credential_chats', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20)->default('user');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('credential_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 30); // created, updated, deleted, tested
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_chats');
        Schema::dropIfExists('credential_logs');
    }
};
