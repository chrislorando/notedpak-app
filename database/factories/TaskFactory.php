<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'description' => fake()->sentence(),
            'project_id' => rand( 1, 10),
            'owner_id' => 1,
            'created_at' => fake()->dateTimeBetween('-2 week', 'now'),
            'completed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
