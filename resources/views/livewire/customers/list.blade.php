<?php

use App\Models\Customer;
use function Livewire\Volt\{state, mount};

state(['search' => '', 'customers' => []]);

mount(function (): void {
    $this->loadCustomers();
});

$loadCustomers = function (): void {
    $this->customers = Customer::where('user_id', auth()->id())
        ->when($this->search, function ($query): void {
            $query->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        })
        ->withCount(['sites', 'tasks'])
        ->orderBy('name')
        ->get()
        ->map(fn($customer): array => [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'sites_count' => $customer->sites_count,
            'jobs_count' => $customer->tasks_count,
        ])
        ->toArray();
};

$deleteCustomer = function ($customerId): void {
    Customer::where('id', $customerId)
        ->where('user_id', auth()->id())
        ->delete();
    $this->loadCustomers();
};

$updated = function ($property): void {
    if ($property === 'search') {
        $this->loadCustomers();
    }
};

?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
        <flux:button variant="primary" href="{{ route('customers.create') }}" wire:navigate>
            Add New Customer
        </flux:button>
    </div>

    <!-- Search -->
    <div class="max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Search customers..."
            icon="magnifying-glass"
        />
    </div>

    <!-- Customers List -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        @if(count($customers) > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($customers as $customer)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('customers.show', $customer['id']) }}" wire:navigate class="hover:text-blue-600">
                                            {{ $customer['name'] }}
                                        </a>
                                    </h3>

                                </div>
                                <div class="mt-1 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <a href="tel:{{ $customer['phone'] }}" class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                        <flux:icon name="phone" class="h-4 w-4" />
                                        {{ $customer['phone'] }}
                                    </a>
                                    @if($customer['email'])
                                        <a href="mailto:{{ $customer['email'] }}" class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                            <flux:icon name="envelope" class="h-4 w-4" />
                                            {{ $customer['email'] }}
                                        </a>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                                    <a href="{{ route('customers.show', $customer['id']) }}?tab=sites" wire:navigate class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                        <flux:icon name="map-pin" class="h-4 w-4" />
                                        {{ $customer['sites_count'] }} site{{ $customer['sites_count'] !== 1 ? 's' : '' }}
                                    </a>
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="clipboard-document-list" class="h-4 w-4" />
                                        {{ $customer['jobs_count'] }} job{{ $customer['jobs_count'] !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:button variant="outline" size="sm" href="{{ route('customers.show', $customer['id']) }}" wire:navigate>
                                    View
                                </flux:button>
                                <flux:button variant="outline" size="sm" href="{{ route('tasks.create.with-customer', $customer['id']) }}" wire:navigate>
                                    New Job
                                </flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="deleteCustomer({{ $customer['id'] }})" class="text-red-600">
                                    <flux:icon name="trash" class="h-4 w-4" />
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                @if($search)
                    <flux:icon name="magnifying-glass" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No customers found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                @else
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No customers yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding your first customer.</p>
                    <div class="mt-6">
                        <flux:button variant="primary" href="{{ route('customers.create') }}" wire:navigate>
                            Add Customer
                        </flux:button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
