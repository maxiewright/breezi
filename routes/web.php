<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', fn() => view('welcome'))->name('home');

Route::middleware(['auth'])->group(function (): void {
    Volt::route('dashboard', 'dashboard')
        ->middleware(['verified'])
        ->name('dashboard');
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Breezi MVP Routes
    Volt::route('customers', 'customers.list')->name('customers.list');
    Volt::route('customers/create', 'customers.create')->name('customers.create');
    Route::post('customers/create', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Volt::route('customers/{customer}', 'customers.show')->name('customers.show');

    Volt::route('sites/create', 'sites.create')->name('sites.create');
    Volt::route('sites/create/{customer?}', 'sites.create')->name('sites.create.with-customer');
    Volt::route('sites/{site}', 'sites.show')->name('sites.show');

    Volt::route('tasks', 'tasks.calendar')->name('tasks.list');
    Volt::route('tasks/calendar', 'tasks.calendar')->name('tasks.calendar');
    Volt::route('tasks/create', 'tasks.create')->name('tasks.create');
    Volt::route('tasks/create/{customer?}', 'tasks.create')->name('tasks.create.with-customer');
    Volt::route('tasks/{task}/edit', 'tasks.edit')->name('tasks.edit');

    // Keep job routes as aliases for backward compatibility
    Volt::route('jobs', 'tasks.calendar')->name('jobs.list');
    Volt::route('jobs/calendar', 'tasks.calendar')->name('jobs.calendar');
    Volt::route('jobs/create', 'tasks.create')->name('jobs.create');
    Volt::route('jobs/create/{customer?}', 'tasks.create')->name('jobs.create.with-customer');
    Volt::route('jobs/{job}/edit', 'tasks.edit')->name('jobs.edit');

    Volt::route('invoices/create', 'invoices.create')->name('invoices.create');
    Volt::route('invoices/{invoice}', 'invoices.show')->name('invoices.show');
    Volt::route('invoices/{invoice}/edit', 'invoices.edit')->name('invoices.edit');

    Volt::route('assets/create', 'assets.create')->name('assets.create');
    Volt::route('assets/create/{site?}', 'assets.create')->name('assets.create.with-site');
});

require __DIR__.'/auth.php';
