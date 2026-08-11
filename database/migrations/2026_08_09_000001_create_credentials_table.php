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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon')->default('fa-solid fa-key');
            $table->string('icon_bg')->nullable();
            $table->string('test_status')->default('success');
            $table->string('test_code')->nullable();
            $table->json('fields');
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
