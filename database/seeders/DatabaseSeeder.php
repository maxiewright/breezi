<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed asset brands and models first
        $this->call(AssetBrandSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('Testing123'),
        ]);

        // Sample data for the test user
        $customers = Customer::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        foreach ($customers as $customer) {
            $sites = Site::factory()->count(2)->create([
                'customer_id' => $customer->id,
            ]);

            foreach ($sites as $site) {
                // Get random brands and models for assets
                $brands = AssetBrand::active()->get();

                for ($i = 0; $i < 2; $i++) {
                    $brand = $brands->random();
                    $model = $brand->assetModels()->active()->inRandomOrder()->first();

                    Asset::create([
                        'site_id' => $site->id,
                        'asset_brand_id' => $brand->id,
                        'asset_model_id' => $model?->id,
                        'name' => fake()->randomElement(['Main Unit', 'Upstairs Unit', 'Downstairs Unit', 'Office Unit', 'Master Bedroom Unit']),
                        'installed_on' => fake()->optional(0.8)->dateTimeBetween('-5 years', 'now'),
                        'serial_number' => fake()->optional(0.9)->regexify('[A-Z0-9]{10}'),
                    ]);
                }

                // Tasks
                $taskToday = Task::create([
                    'site_id' => $site->id,
                    'type' => 'maintenance',
                    'title' => 'Maintenance Visit',
                    'description' => 'Routine maintenance and filter check.',
                    'status' => 'scheduled',
                    'scheduled_at' => Carbon::now()->setTime(10, 0, 0),
                ]);

                $taskFuture = Task::create([
                    'site_id' => $site->id,
                    'type' => 'inspection',
                    'title' => 'Follow-up Inspection',
                    'description' => 'Check refrigerant levels and performance.',
                    'status' => 'scheduled',
                    'scheduled_at' => Carbon::now()->addDays(3)->setTime(14, 30, 0),
                ]);

                // Invoice for today task (draft)
                $invoice = Invoice::create([
                    'task_id' => $taskToday->id,
                    'number' => 'INV-'.fake()->unique()->numberBetween(1000, 9999),
                    'status' => 'draft',
                    'total' => 0,
                    'notes' => 'Auto-generated sample invoice.',
                ]);

                // A couple invoice items
                $items = [
                    ['description' => 'Service Call', 'quantity' => 1, 'unit_price' => 75.00],
                    ['description' => 'Air Filter', 'quantity' => 2, 'unit_price' => 15.00],
                ];

                foreach ($items as $it) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $it['description'],
                        'quantity' => $it['quantity'],
                        'unit_price' => $it['unit_price'],
                        'total_price' => $it['quantity'] * $it['unit_price'],
                    ]);
                }

                // Update invoice total
                $invoice->update([
                    'total' => $invoice->items()->sum('total_price'),
                ]);
            }
        }
    }
}
