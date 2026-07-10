<?php

namespace Database\Factories;

use App\Models\HedrasoulSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HedrasoulSessionFactory extends Factory
{
    protected $model = HedrasoulSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Test Session',
            'status' => 'active',
            'topic' => 'general',
            'task_count' => 0,
            'approval_count' => 0,
            'last_autonomy_mode' => 'copilot',
            'opened_at' => now(),
        ];
    }
}
