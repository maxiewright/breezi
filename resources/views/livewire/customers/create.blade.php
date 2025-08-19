<?php

use App\Models\Customer;
use function Livewire\Volt\{state, rules};

state([
    'name' => '',
    'phone' => '',
    'email' => '',
]);

rules([
    'name' => 'required|string|max:255',
    'phone' => 'required|string|max:20',
    'email' => 'nullable|email|max:255',
]);

$save = function (): void {
    $this->validate();

    Customer::create([
        'user_id' => auth()->id(),
        'name' => $this->name,
        'phone' => $this->phone,
        'email' => $this->email ?: null,
    ]);

    $this->redirect(route('customers.list'));
};

?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Customer</h1>
        <flux:button variant="outline" href="{{ route('customers.list') }}" wire:navigate>
            Back to Customers
        </flux:button>
    </div>

    <!-- Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:field label="Customer Name" required>
                    <flux:input
                        wire:model="name"
                        placeholder="Enter customer name"
                        error="{{ $errors->first('name') }}"
                    />
                </flux:field>
            </div>

            <div>
                <flux:field label="Phone Number" required>
                    <flux:input
                        wire:model="phone"
                        placeholder="Enter phone number"
                        error="{{ $errors->first('phone') }}"
                    />
                </flux:field>
            </div>

            <div>
                <flux:field label="Email Address (Optional)">
                    <flux:input
                        wire:model="email"
                        type="email"
                        placeholder="Enter email address"
                        error="{{ $errors->first('email') }}"
                    />
                </flux:field>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <flux:button variant="outline" href="{{ route('customers.list') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Customer
                </flux:button>
            </div>
        </form>
    </div>
</div>
