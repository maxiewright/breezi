<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Site;
use App\Models\User;

test('user can view job creation page', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get('/tasks/create')
        ->assertSuccessful()
        ->assertSeeLivewire('tasks.create');
});

test('guests are redirected to login', function (): void {
    $this->get('/tasks/create')
        ->assertRedirect('/login');
});

test('user can view job creation page with pre-filled customer', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $site = Site::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($user)
        ->get("/tasks/create/{$customer->id}")
        ->assertSuccessful()
        ->assertSeeLivewire('tasks.create')
        ->assertSee($customer->name);
});
