<?php

use App\Models\Site;
use App\Models\Customer;
use function Livewire\Volt\{state, rules, mount};

state([
    'customer_id' => '',
    'address_line_1' => '',
    'city' => '',
    'notes' => '',
    'customer' => null,
]);

rules([
    'customer_id' => 'required|exists:customers,id',
    'address_line_1' => 'required|string|max:255',
    'city' => 'required|string|max:255',
    'notes' => 'nullable|string',
]);

mount(function (): void {
    // Check if a customer was passed via route parameter
    $customerId = request()->route('customer');
    if ($customerId) {
        $this->customer_id = $customerId;
    }

    // Check if a customer was passed via query parameter (fallback)
    if (!$this->customer_id) {
        $customerId = request()->query('customer');
        if ($customerId) {
            $this->customer_id = $customerId;
        }
    }

    // Load customer data if customer_id is set
    if ($this->customer_id) {
        $this->customer = Customer::where('id', $this->customer_id)
            ->where('user_id', auth()->id())
            ->first();
    }
});

$save = function (): void {
    $this->validate();

    Site::create([
        'customer_id' => $this->customer_id,
        'address_line_1' => $this->address_line_1,
        'city' => $this->city,
        'notes' => $this->notes ?: null,
    ]);

    $this->redirect(route('customers.show', $this->customer_id));
};

?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Site</h1>
        @if($customer)
            <flux:button variant="outline" href="{{ route('customers.show', $customer) }}" wire:navigate>
                Back to {{ $customer->name }}
            </flux:button>
        @else
            <flux:button variant="outline" href="{{ route('customers.list') }}" wire:navigate>
                Back to Customers
            </flux:button>
        @endif
    </div>

    <!-- Customer Info Display (when pre-selected) -->
    @if($customer)
        <div class="rounded-lg border border-gray-200 bg-blue-50 p-4 dark:border-gray-600 dark:bg-blue-900/20">
            <div class="flex items-center gap-3">
                <flux:icon name="user" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                <div>
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100">Adding site for {{ $customer->name }}</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ $customer->phone }} @if($customer->email) • {{ $customer->email }} @endif</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <!-- Customer Selection (only show if no customer pre-selected) -->
            @if(!$customer)
                <div>
                    <flux:field label="Customer" required>
                        <select
                            wire:model="customer_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        >
                            <option value="">Select a customer</option>
                            @foreach(Customer::where('user_id', auth()->id())->orderBy('name')->get() as $customerOption)
                                <option value="{{ $customerOption->id }}">
                                    {{ $customerOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </flux:field>
                </div>
            @endif

            <!-- Address -->
            <div>
                <flux:field label="Address" required>
                    <flux:input
                        wire:model="address_line_1"
                        placeholder="Enter street address"
                        error="{{ $errors->first('address_line_1') }}"
                    />
                </flux:field>
            </div>

            <!-- City -->
            <div>
                <flux:field label="City" required>
                    <flux:input
                        wire:model="city"
                        placeholder="Enter city"
                        error="{{ $errors->first('city') }}"
                    />
                </flux:field>
            </div>

            <!-- Notes -->
            <div>
                <flux:field label="Notes (Optional)">
                    <flux:textarea
                        wire:model="notes"
                        placeholder="Enter any additional notes about this site"
                        rows="3"
                        error="{{ $errors->first('notes') }}"
                    />
                </flux:field>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                @if($customer)
                    <flux:button variant="outline" href="{{ route('customers.show', $customer) }}" wire:navigate>
                        Cancel
                    </flux:button>
                @else
                    <flux:button variant="outline" href="{{ route('customers.list') }}" wire:navigate>
                        Cancel
                    </flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    Create Site
                </flux:button>
            </div>
        </form>
    </div>
</div>
