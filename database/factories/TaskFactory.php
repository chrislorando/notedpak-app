<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
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
            'id' => fake()->uuid(),
            'description' => fake()->sentence(),
            'project_id' => Project::inRandomOrder()->first()->id,
            'owner_id' => User::first()->id,
            'created_at' => fake()->dateTimeBetween('-2 week', 'now'),
            // 'completed_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
