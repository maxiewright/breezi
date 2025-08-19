<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'sent', 'paid'];

        return [
            'task_id' => Task::factory(),
            'number' => 'INV-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => fake()->randomElement($statuses),
            'total' => fake()->randomFloat(2, 50, 500),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}
