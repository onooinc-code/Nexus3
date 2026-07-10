<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexing Messages
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasIndex('messages', 'messages_conversation_id_index')) {
                $table->index('conversation_id', 'messages_conversation_id_index');
            }
            if (! Schema::hasIndex('messages', 'messages_created_at_index')) {
                $table->index('created_at', 'messages_created_at_index');
            }
        });

        Schema::table('hedrasoul_messages', function (Blueprint $table) {
            if (! Schema::hasIndex('hedrasoul_messages', 'hedrasoul_messages_session_id_index')) {
                $table->index('session_id', 'hedrasoul_messages_session_id_index');
            }
        });

        // Indexing Memories - using contact_id
        Schema::table('memories', function (Blueprint $table) {
            if (! Schema::hasIndex('memories', 'memories_contact_id_index')) {
                $table->index('contact_id', 'memories_contact_id_index');
            }
        });

        // Indexing Conversations - using contact_id
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasIndex('conversations', 'conversations_contact_id_index')) {
                $table->index('contact_id', 'conversations_contact_id_index');
            }
            if (! Schema::hasIndex('conversations', 'conversations_status_index')) {
                $table->index('status', 'conversations_status_index');
            }
        });

        // Indexing Workflows
        Schema::table('workflow_executions', function (Blueprint $table) {
            if (! Schema::hasIndex('workflow_executions', 'workflow_executions_workflow_id_index')) {
                $table->index('workflow_id', 'workflow_executions_workflow_id_index');
            }
            if (! Schema::hasIndex('workflow_executions', 'workflow_executions_status_index')) {
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
