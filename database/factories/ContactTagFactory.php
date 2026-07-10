<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactTag>
 */
class ContactTagFactory extends Factory
{
    protected $model = ContactTag::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'name' => fake()->word(),
            'color' => fake()->safeHexColor(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
