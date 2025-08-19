<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetModel>
 */
class AssetModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $models = [
            'ComfortLink II', 'Infinity Series', 'Performance Series', 'Comfort Series',
            'XV20i', 'XL18i', 'XL16i', 'XL14i', 'XL13i',
            'Elite Series', 'Merit Series', 'Signature Series',
            'Prestige Series', 'Professional Series', 'Classic Series',
            'GSXC18', 'GSX14', 'GSX13', 'GMSS96', 'GMEC96',
            'Silver 14', 'Silver 16', 'Platinum 18', 'Platinum 20',
        ];

        return [
            'asset_brand_id' => \App\Models\AssetBrand::factory(),
            'name' => fake()->randomElement($models),
            'description' => fake()->optional(0.6)->sentence(),
            'model_number' => fake()->optional(0.8)->regexify('[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}'),
            'btu_rating' => fake()->optional(0.7)->randomFloat(2, 12000, 60000),
            'efficiency_rating' => fake()->optional(0.8)->randomElement(['13 SEER', '14 SEER', '16 SEER', '18 SEER', '20 SEER', '22 SEER']),
            'is_active' => fake()->boolean(95),
        ];
    }
}
