<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('zone')->default('center'); // top, bottom, left, right, center
            $table->string('type');
            $table->integer('order')->default(0);
            $table->boolean('is_open')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};
