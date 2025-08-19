<?php

declare(strict_types=1);

use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetModel;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('complete application flow works end-to-end', function (): void {
    // 1. Create a user
    $user = User::factory()->create();

    // 2. Create a customer
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    expect($customer->user_id)->toBe($user->id);
    expect($customer->slug)->not->toBeEmpty();

    // 3. Create a site for the customer
    $site = Site::factory()->create(['customer_id' => $customer->id]);
    expect($site->customer_id)->toBe($customer->id);
    expect($site->slug)->not->toBeEmpty();

    // 4. Create asset brand and model
    $brand = AssetBrand::factory()->create();
    $model = AssetModel::factory()->create(['asset_brand_id' => $brand->id]);
    expect($brand->slug)->not->toBeEmpty();
    expect($model->asset_brand_id)->toBe($brand->id);

    // 5. Create an asset at the site
    $asset = Asset::factory()->create([
        'site_id' => $site->id,
        'asset_brand_id' => $brand->id,
        'asset_model_id' => $model->id,
    ]);
    expect($asset->site_id)->toBe($site->id);
    expect($asset->asset_brand_id)->toBe($brand->id);
    expect($asset->asset_model_id)->toBe($model->id);

    // 6. Create a task for the site
    $task = Task::factory()->create(['site_id' => $site->id]);
    expect($task->site_id)->toBe($site->id);
    expect($task->slug)->not->toBeEmpty();

    // 7. Link asset to task
    $asset->tasks()->attach($task, [
        'service_notes' => 'Test service notes',
        'condition_before' => 'Good',
        'condition_after' => 'Excellent',
        'filter_changed' => true,
        'parts_replaced' => 'Filter',
        'parts_list' => 'Filter, Oil',
        'labor_hours' => 2.5,
    ]);

    expect($asset->tasks)->toHaveCount(1);
    expect($task->assets)->toHaveCount(1);
    expect($asset->tasks->first()->pivot->service_notes)->toBe('Test service notes');

    // 8. Create an invoice for the task
    $invoice = Invoice::factory()->create(['task_id' => $task->id]);
    expect($invoice->task_id)->toBe($task->id);
    expect($invoice->slug)->not->toBeEmpty();

    // 9. Verify relationships work correctly
    expect($task->invoice->id)->toBe($invoice->id);
    expect($invoice->task->id)->toBe($task->id);
    expect($task->site->id)->toBe($site->id);
    expect($site->customer->id)->toBe($customer->id);
    expect($customer->user->id)->toBe($user->id);

    // 10. Verify counts work
    expect($customer->sites)->toHaveCount(1);
    expect($customer->tasks)->toHaveCount(1);
    expect($customer->assets)->toHaveCount(1);
    expect($site->assets)->toHaveCount(1);
    expect($site->tasks)->toHaveCount(1);

    // 11. Verify backward compatibility aliases work
    expect($customer->jobs)->toHaveCount(1);
    expect($site->jobs)->toHaveCount(1);
    expect($asset->serviceJobs)->toHaveCount(1);
    expect($invoice->job->id)->toBe($task->id);
});

test('slug generation works correctly for all models', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);
    $brand = AssetBrand::factory()->create();
    $model = AssetModel::factory()->create(['asset_brand_id' => $brand->id]);
    $asset = Asset::factory()->create([
        'site_id' => $site->id,
        'asset_brand_id' => $brand->id,
        'asset_model_id' => $model->id,
    ]);
    $task = Task::factory()->create(['site_id' => $site->id]);
    $invoice = Invoice::factory()->create(['task_id' => $task->id]);

    // Verify all models have slugs
    expect($customer->slug)->not->toBeEmpty();
    expect($site->slug)->not->toBeEmpty();
    expect($brand->slug)->not->toBeEmpty();
    expect($model->slug)->not->toBeEmpty();
    expect($asset->slug)->not->toBeEmpty();
    expect($task->slug)->not->toBeEmpty();
    expect($invoice->slug)->not->toBeEmpty();

    // Verify slugs are URL-friendly
    expect($customer->slug)->not->toContain(' ');
    expect($site->slug)->not->toContain(' ');
    expect($brand->slug)->not->toContain(' ');
    expect($model->slug)->not->toContain(' ');
    expect($asset->slug)->not->toContain(' ');
    expect($task->slug)->not->toContain(' ');
    expect($invoice->slug)->not->toContain(' ');
});

test('enum casting works correctly', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    // Test Task enums
    $task = Task::factory()->create([
        'site_id' => $site->id,
        'type' => 'service',
        'status' => 'scheduled',
    ]);

    expect($task->type)->toBeInstanceOf(\App\Enums\TaskType::class);
    expect($task->status)->toBeInstanceOf(\App\Enums\TaskStatus::class);
    expect($task->type->value)->toBe('service');
    expect($task->status->value)->toBe('scheduled');

    // Test Invoice enum
    $invoice = Invoice::factory()->create([
        'task_id' => $task->id,
        'status' => 'draft',
    ]);

    expect($invoice->status)->toBeInstanceOf(\App\Enums\InvoiceStatus::class);
    expect($invoice->status->value)->toBe('draft');
});

test('factory data is realistic and consistent', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);
    $brand = AssetBrand::factory()->create();
    $model = AssetModel::factory()->create(['asset_brand_id' => $brand->id]);
    $asset = Asset::factory()->create([
        'site_id' => $site->id,
        'asset_brand_id' => $brand->id,
        'asset_model_id' => $model->id,
    ]);
    $task = Task::factory()->create(['site_id' => $site->id]);

    // Verify data types and constraints
    expect($customer->name)->toBeString();
    expect($customer->name)->not->toBeEmpty();
    expect($site->address_line_1)->toBeString();
    expect($site->city)->toBeString();
    expect($brand->name)->toBeString();
    expect($model->name)->toBeString();
    expect($asset->name)->toBeString();
    expect($asset->serial_number)->toBeString();
    expect($task->title)->toBeString();

    // Verify foreign key relationships are valid
    expect($customer->user_id)->toBe($user->id);
    expect($site->customer_id)->toBe($customer->id);
    expect($asset->site_id)->toBe($site->id);
    expect($asset->asset_brand_id)->toBe($brand->id);
    expect($asset->asset_model_id)->toBe($model->id);
    expect($task->site_id)->toBe($site->id);
});

test('route model binding works with slugs', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);
    $brand = AssetBrand::factory()->create();
    $model = AssetModel::factory()->create(['asset_brand_id' => $brand->id]);
    $asset = Asset::factory()->create([
        'site_id' => $site->id,
        'asset_brand_id' => $brand->id,
        'asset_model_id' => $model->id,
    ]);
    $task = Task::factory()->create(['site_id' => $site->id]);
    $invoice = Invoice::factory()->create(['task_id' => $task->id]);

    // Verify route key names are set to slug
    expect($customer->getRouteKeyName())->toBe('slug');
    expect($site->getRouteKeyName())->toBe('slug');
    expect($brand->getRouteKeyName())->toBe('slug');
    expect($model->getRouteKeyName())->toBe('slug');
    expect($asset->getRouteKeyName())->toBe('slug');
    expect($task->getRouteKeyName())->toBe('slug');
    expect($invoice->getRouteKeyName())->toBe('slug');

    // Verify models can be found by slug
    expect(Customer::where('slug', $customer->slug)->first()->id)->toBe($customer->id);
    expect(Site::where('slug', $site->slug)->first()->id)->toBe($site->id);
    expect(AssetBrand::where('slug', $brand->slug)->first()->id)->toBe($brand->id);
    expect(AssetModel::where('slug', $model->slug)->first()->id)->toBe($model->id);
    expect(Asset::where('slug', $asset->slug)->first()->id)->toBe($asset->id);
    expect(Task::where('slug', $task->slug)->first()->id)->toBe($task->id);
    expect(Invoice::where('slug', $invoice->slug)->first()->id)->toBe($invoice->id);
});

test('asset brand and model scopes work correctly', function (): void {
    $brand1 = AssetBrand::factory()->create(['is_active' => true]);
    $brand2 = AssetBrand::factory()->create(['is_active' => false]);
    $brand3 = AssetBrand::factory()->create(['is_active' => true]);

    $model1 = AssetModel::factory()->create(['asset_brand_id' => $brand1->id, 'is_active' => true]);
    $model2 = AssetModel::factory()->create(['asset_brand_id' => $brand1->id, 'is_active' => false]);
    $model3 = AssetModel::factory()->create(['asset_brand_id' => $brand2->id, 'is_active' => true]);

    // Test active scopes
    $activeBrands = AssetBrand::active()->get();
    $activeModels = AssetModel::active()->get();

    expect($activeBrands)->toHaveCount(2);
    expect($activeModels)->toHaveCount(2);
    expect($activeBrands->every(fn ($brand) => $brand->is_active))->toBeTrue();
    expect($activeModels->every(fn ($model) => $model->is_active))->toBeTrue();

    // Test relationships
    expect($brand1->assetModels)->toHaveCount(2);
    expect($brand2->assetModels)->toHaveCount(1);
    expect($brand3->assetModels)->toHaveCount(0);
});

test('task scheduling and completion works correctly', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $scheduledDate = now()->addDays(5);
    $completedDate = now();

    $task = Task::factory()->create([
        'site_id' => $site->id,
        'status' => 'scheduled',
        'scheduled_at' => $scheduledDate,
        'completed_at' => null,
    ]);

    expect($task->scheduled_at->toDateString())->toBe($scheduledDate->toDateString());
    expect($task->completed_at)->toBeNull();

    // Complete the task
    $task->update([
        'status' => 'completed',
        'completed_at' => $completedDate,
    ]);

    expect($task->fresh()->status->value)->toBe('completed');
    expect($task->fresh()->completed_at->toDateString())->toBe($completedDate->toDateString());
});

test('invoice creation and status management works', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);
    $task = Task::factory()->create(['site_id' => $site->id]);

    // Create invoice with different statuses
    $draftInvoice = Invoice::factory()->create([
        'task_id' => $task->id,
        'status' => 'draft',
        'total' => 150.00,
    ]);

    $sentInvoice = Invoice::factory()->create([
        'task_id' => $task->id,
        'status' => 'sent',
        'total' => 200.00,
    ]);

    $paidInvoice = Invoice::factory()->create([
        'task_id' => $task->id,
        'status' => 'paid',
        'total' => 250.00,
    ]);

    expect($draftInvoice->status->value)->toBe('draft');
    expect($sentInvoice->status->value)->toBe('sent');
    expect($paidInvoice->status->value)->toBe('paid');

    expect($draftInvoice->total)->toBe('150.00');
    expect($sentInvoice->total)->toBe('200.00');
    expect($paidInvoice->total)->toBe('250.00');

    // Verify relationships
    expect($draftInvoice->task->id)->toBe($task->id);
    expect($draftInvoice->customer->id)->toBe($customer->id);
    expect($draftInvoice->site->id)->toBe($site->id);
});
