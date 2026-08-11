<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Seeder;

class ErtugrulBrowserAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create(['name' => 'System Agent Owner', 'email' => 'agent-owner@nexus.local']);

        Agent::updateOrCreate(
            ['key' => 'ertugrul_browser_agent'],
            [
                'name' => 'Ertugrul Browser Orchestrator',
                'key' => 'ertugrul_browser_agent',
                'description' => 'Autonomous Chrome browser controller, DOM observer, and Vision Captcha solver.',
                'type' => 'specialized',
                'status' => Agent::STATUS_ACTIVE,
                'is_active' => true,
                'is_system' => true,
                'owner_id' => $user->id,
                'rate_limit_per_minute' => 120,
                'metadata' => [
                    'extension_bridge' => 'Nexus Agentic Browser Bridge v1.0',
                    'capabilities' => [
                        'dom_mutation_listen',
                        'vision_captcha_solve',
                        'human_typing_emulation',
                        'cookie_session_reuse',
                        'tab_management',
                    ],
                ],
            ]
        );

        $this->command->info('✅ ErtugrulBrowserAgentSeeder complete — ertugrul_browser_agent registered.');
    }
}
