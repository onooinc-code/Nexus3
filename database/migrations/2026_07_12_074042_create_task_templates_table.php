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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Task attributes template
            $table->string('task_type')->default('agent');
            $table->string('title_template');
            $table->text('description_template')->nullable();

            // Payload and variables
            $table->json('payload_template')->nullable();
            $table->json('expected_variables')->nullable(); // List of required {vars}

            // Default configuration
            $table->integer('default_priority')->default(0);
            $table->string('agent_type')->nullable(); // Default agent for this template

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};
