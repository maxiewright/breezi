<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;

test('user can view job calendar', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get('/tasks/calendar')
        ->assertSuccessful()
        ->assertSeeLivewire('tasks.calendar');
});

test('guests are redirected to login when viewing calendar', function (): void {
    $this->get('/tasks/calendar')
        ->assertRedirect('/login');
});

test('calendar shows jobs for current month', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    // Create a job for today
    $todayJob = Task::factory()->create([
        'site_id' => $site->id,
        'scheduled_at' => now(),
        'status' => 'scheduled',
    ]);

    // Create a job for tomorrow
    $tomorrowJob = Task::factory()->create([
        'site_id' => $site->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
    ]);

    $this->actingAs($user)
        ->get('/jobs/calendar')
        ->assertSuccessful()
        ->assertSee($todayJob->title)
        ->assertSee($tomorrowJob->title);
});

test('calendar navigation works', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get('/jobs/calendar')
        ->assertSuccessful()
        ->assertSee('Today')
        ->assertSee('Previous')
        ->assertSee('Next');
});

test('calendar shows correct month and year', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get('/jobs/calendar')
        ->assertSuccessful()
        ->assertSee(now()->format('F Y'));
});

test('calendar shows job count badges', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    // Create multiple jobs for today
    Task::factory()->count(3)->create([
        'site_id' => $site->id,
        'scheduled_at' => now(),
        'status' => 'scheduled',
    ]);

    $this->actingAs($user)
        ->get('/jobs/calendar')
        ->assertSuccessful()
        ->assertSee('3'); // Should show job count badge
});
