<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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
            'name' => fake()->randomElement([
                'Website Redesign',
                'Mobile App Development',
                'Marketing Campaign',
                'Database Migration',
                'Research & Analysis',
            ]),
            'description' => fake()->text(),
            'color' => fake()->hexColor(),
            'user_id' => User::first()->id,
        ];
    }
}
