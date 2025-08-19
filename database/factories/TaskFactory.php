<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
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
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        $titles = [
            'AC Maintenance Service',
            'AC Repair - No Cooling',
            'AC Installation',
            'Filter Replacement',
            'Refrigerant Recharge',
            'Thermostat Installation',
            'Emergency AC Repair',
            'Duct Cleaning',
            'AC Inspection',
            'Compressor Repair',
        ];

        return [
            'site_id' => Site::factory(),
            'type' => fake()->randomElement(['service', 'repair', 'maintenance', 'install', 'inspection']),
            'title' => fake()->randomElement($titles),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => fake()->randomElement($statuses),
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'completed_at' => fake()->optional(0.3)->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
