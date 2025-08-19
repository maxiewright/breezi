<?php

declare(strict_types=1);

use App\Models\User;

test('user can view dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSeeLivewire('dashboard');
});
