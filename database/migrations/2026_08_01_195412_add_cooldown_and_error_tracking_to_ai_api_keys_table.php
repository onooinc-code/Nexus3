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
        Schema::table('ai_api_keys', function (Blueprint $table) {
            $table->timestamp('cooldown_until')->nullable()->after('expires_at')->comment('Timestamp until which this key is flagged in cooldown/exhausted state');
            $table->integer('error_count')->default(0)->after('cooldown_until')->comment('Consecutive failure or exhaustion count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_api_keys', function (Blueprint $table) {
            $table->dropColumn(['cooldown_until', 'error_count']);
        });
    }
};
