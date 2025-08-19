<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'asset_brand_id' => \App\Models\AssetBrand::factory(),
            'asset_model_id' => \App\Models\AssetModel::factory(),
            'name' => fake()->randomElement(['Living Room Unit', 'Bedroom Unit', 'Kitchen Unit', 'Office Unit', 'Basement Unit']),
            'installed_on' => fake()->optional(0.8)->dateTimeBetween('-5 years', 'now'),
            'serial_number' => fake()->optional(0.9)->regexify('[A-Z0-9]{10}'),
        ];
    }
}
