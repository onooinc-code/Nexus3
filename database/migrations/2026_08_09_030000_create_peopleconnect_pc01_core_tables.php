<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PeopleConnect Nexus 3 Architecture (PC-01).
     */
    public function up(): void
    {
        // 1. Contact Profiles Table Extensions / Verification
        // Note: Base 'contacts' table exists. Adding PC-01 specific columns if not present.
        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'relationship_type')) {
                $table->string('relationship_type', 50)->nullable()->after('type'); // e.g., family, friend, business, client, hostile
            }
            if (! Schema::hasColumn('contacts', 'priority_level')) {
                $table->unsignedTinyInteger('priority_level')->default(3)->after('relationship_type'); // 1 (Critical/VIP) to 5 (Low)
            }
            if (! Schema::hasColumn('contacts', 'fb_id')) {
                $table->string('fb_id', 100)->nullable()->unique()->after('waha_contact_id');
            }
            if (! Schema::hasColumn('contacts', 'summary')) {
                $table->text('summary')->nullable()->after('attributes');
            }
        });

        // 2. Facts & Traits Table
        // Stores extracted structured facts and personal traits per contact
        Schema::create('contact_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('category', 50)->default('general'); // bio, preference, work, habit, relationship, background
            $table->string('fact_key', 100);
            $table->text('fact_value');
            $table->decimal('confidence', 5, 2)->default(1.00); // 0.00 to 1.00
            $table->string('source', 50)->default('manual'); // whatsapp_import, fb_chat_import, fb_xml, waha_live, manual, ai_extracted
            $table->string('source_ref', 255)->nullable(); // e.g. message_id or file reference
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['contact_id', 'category']);
            $table->index(['contact_id', 'fact_key']);
        });

        // 3. Stances & Principles Table
        // Defines Hedra's persona stance, boundary rules, and interaction guidelines per contact/topic
        Schema::create('contact_stances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('topic', 100); // e.g. money, political_views, family_matters, business_deals, general
            $table->string('hedra_stance', 50)->default('neutral'); // supportive, firm, distant, cautious, strict_boundary, hostile_defense
            $table->json('boundary_rules')->nullable(); // array of non-negotiable rules for AI twin
            $table->text('past_incidents')->nullable(); // history or rationale shaping this stance
            $table->timestamps();

            $table->unique(['contact_id', 'topic']);
        });

        // 4. Dialogue Tasks Table
        // Manages goal-driven interactions, CRM tasks, and automated dialogue workflows
        Schema::create('dialogue_tasks', function (Blueprint $table) {
            $table->string('task_id', 64)->primary(); // e.g. dtask_uuid or custom string id
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('goal_type', 50); // outgoing_goal, incoming_handler, info_collection, relationship_check
            $table->text('target_outcome'); // What needs to be achieved
            $table->string('current_state', 50)->default('initiated'); // initiated, in_progress, goal_satisfied, paused_approval, closed, escalated
            $table->string('status', 30)->default('active'); // active, paused, completed, cancelled, failed
            $table->timestamp('last_message_at')->nullable();
            $table->json('checkpoint_data')->nullable(); // track progress milestones and extracted data during dialogue
            $table->text('hedra_approval_reason')->nullable(); // filled when state is paused_approval
            $table->timestamps();

            $table->index(['contact_id', 'status']);
            $table->index('current_state');
        });

        // 5. Message History Index Table
        // High-performance index for unified cross-platform messaging history (WhatsApp, FB, etc.)
        Schema::create('message_history_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('platform', 30)->default('whatsapp'); // whatsapp, fb_messenger, sms, instagram, system
            $table->string('sender_type', 20)->default('contact'); // user (Hedra), contact, agent, system
            $table->text('message_text');
            $table->timestamp('timestamp')->index();
            $table->string('sentiment', 30)->nullable(); // positive, neutral, negative, hostile
            $table->json('topics')->nullable(); // array of detected topics
            $table->string('source_id', 255)->nullable(); // external platform message ID or hash
            $table->timestamps();

            $table->index(['contact_id', 'platform', 'timestamp']);
            $table->unique(['platform', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_history_index');
        Schema::dropIfExists('dialogue_tasks');
        Schema::dropIfExists('contact_stances');
        Schema::dropIfExists('contact_facts');

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'summary')) {
                $table->dropColumn('summary');
            }
            if (Schema::hasColumn('contacts', 'fb_id')) {
                $table->dropColumn('fb_id');
            }
            if (Schema::hasColumn('contacts', 'priority_level')) {
                $table->dropColumn('priority_level');
            }
            if (Schema::hasColumn('contacts', 'relationship_type')) {
                $table->dropColumn('relationship_type');
            }
        });
    }
};
