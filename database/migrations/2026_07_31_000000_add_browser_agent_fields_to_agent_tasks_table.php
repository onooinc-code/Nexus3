<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_tasks', 'origin_agent_id')) {
                $table->string('origin_agent_id')->nullable()->after('agent_id')->index();
            }
            if (! Schema::hasColumn('agent_tasks', 'target_agent_id')) {
                $table->string('target_agent_id')->default('ertugrul_browser_agent')->after('origin_agent_id')->index();
            }
            if (! Schema::hasColumn('agent_tasks', 'task_type')) {
                $table->enum('task_type', ['immediate', 'recurring', 'event_driven', 'pipeline'])->default('immediate')->after('target_agent_id');
            }
            if (! Schema::hasColumn('agent_tasks', 'dynamic_system_instruction')) {
                $table->longText('dynamic_system_instruction')->nullable()->after('task_type');
            }
            if (! Schema::hasColumn('agent_tasks', 'execution_proof')) {
                $table->json('execution_proof')->nullable()->after('dynamic_system_instruction');
            }
            if (! Schema::hasColumn('agent_tasks', 'dom_event_trigger')) {
                $table->json('dom_event_trigger')->nullable()->after('execution_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'origin_agent_id',
                'target_agent_id',
                'task_type',
                'dynamic_system_instruction',
                'execution_proof',
                'dom_event_trigger',
            ]);
        });
    }
};
