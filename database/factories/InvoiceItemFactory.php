<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $descriptions = [
            'AC Maintenance Service',
            'Filter Replacement',
            'Refrigerant Recharge',
            'Thermostat Installation',
            'Compressor Repair',
            'Duct Cleaning',
            'Emergency Service Call',
            'Parts and Labor',
            'Travel Time',
            'Diagnostic Fee',
        ];

        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->randomFloat(2, 25, 150);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->randomElement($descriptions),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];
    }
}
