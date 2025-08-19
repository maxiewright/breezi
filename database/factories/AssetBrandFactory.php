<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetBrand>
 */
class AssetBrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['Carrier', 'Trane', 'Lennox', 'Rheem', 'Goodman', 'American Standard', 'York', 'Daikin', 'Mitsubishi Electric', 'LG'];

        return [
            'name' => fake()->unique()->randomElement($brands),
            'description' => fake()->optional(0.7)->sentence(),
            'is_active' => fake()->boolean(90),
        ];
    }
}
