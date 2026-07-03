<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Indexing Messages
        Schema::table('messages', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM messages");
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('messages_conversation_id_index', $indexNames)) {
                $table->index('conversation_id', 'messages_conversation_id_index');
            }
            if (!in_array('messages_created_at_index', $indexNames)) {
                $table->index('created_at', 'messages_created_at_index');
            }
        });

        Schema::table('hedrasoul_messages', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM hedrasoul_messages");
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('hedrasoul_messages_session_id_index', $indexNames)) {
                $table->index('session_id', 'hedrasoul_messages_session_id_index');
            }
        });

        // Indexing Memories - using contact_id
        Schema::table('memories', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM memories");
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('memories_contact_id_index', $indexNames)) {
                $table->index('contact_id', 'memories_contact_id_index');
            }
        });

        // Indexing Conversations - using contact_id
        Schema::table('conversations', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM conversations");
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('conversations_contact_id_index', $indexNames)) {
                $table->index('contact_id', 'conversations_contact_id_index');
            }
            if (!in_array('conversations_status_index', $indexNames)) {
                $table->index('status', 'conversations_status_index');
            }
        });

        // Indexing Workflows
        Schema::table('workflow_executions', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM workflow_executions");
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('workflow_executions_workflow_id_index', $indexNames)) {
                $table->index('workflow_id', 'workflow_executions_workflow_id_index');
            }
            if (!in_array('workflow_executions_status_index', $indexNames)) {
                $table->index('status', 'workflow_executions_status_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_id_index');
            $table->dropIndex('messages_created_at_index');
        });
        Schema::table('hedrasoul_messages', function (Blueprint $table) {
            $table->dropIndex('hedrasoul_messages_session_id_index');
        });
        Schema::table('memories', function (Blueprint $table) {
            $table->dropIndex('memories_contact_id_index');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_contact_id_index');
            $table->dropIndex('conversations_status_index');
        });
        Schema::table('workflow_executions', function (Blueprint $table) {
            $table->dropIndex('workflow_executions_workflow_id_index');
            $table->dropIndex('workflow_executions_status_index');
        });
    }
};
