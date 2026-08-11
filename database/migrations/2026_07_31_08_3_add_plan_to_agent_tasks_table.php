<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_tasks', 'plan')) {
                $table->json('plan')->nullable()->after('execution_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('agent_tasks', 'plan')) {
                $table->dropColumn('plan');
            }
        });
    }
};
