<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 3), true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['general', 'sales', 'support', 'ai', 'memory']),
            'description' => fake()->sentence(10),
            'is_active' => true,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
