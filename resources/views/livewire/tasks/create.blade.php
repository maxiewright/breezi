<?php

use App\Models\Customer;
use App\Models\Site;
use App\Models\Task;
use function Livewire\Volt\{state, rules, mount};

state([
    'customer_id' => '',
    'site_id' => '',
    'title' => '',
    'description' => '',
    'status' => 'scheduled',
    'scheduled_at' => '',
    'scheduled_time' => '09:00',
    'customers' => [],
    'sites' => [],
    'customer' => null,
    'site' => null,
]);

rules([
    'customer_id' => 'required|exists:customers,id',
    'site_id' => 'required|exists:sites,id',
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'status' => 'required|in:scheduled,in_progress,completed,cancelled',
    'scheduled_at' => 'required|date',
    'scheduled_time' => 'required',
]);

mount(function (): void {
    $this->customers = Customer::where('user_id', auth()->id())
        ->orderBy('name')
        ->get(['id', 'name', 'phone', 'email'])
        ->toArray();

    // If a date is provided from the calendar, use it
    if ($date = request()->query('date')) {
        try {
            $this->scheduled_at = \Illuminate\Support\Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            $this->scheduled_at = now()->format('Y-m-d');
        }
    }

    // Check if a site was passed via route parameter
    $siteId = request()->route('site');
    if ($siteId) {
        $site = Site::where('id', $siteId)
            ->whereHas('customer', function ($query): void {
                $query->where('user_id', auth()->id());
            })
            ->with('customer')
            ->first();

        if ($site) {
            $this->customer_id = $site->customer_id;
            $this->site_id = $site->id;
            $this->customer = $site->customer;
            $this->site = $site;
        }
    }

    // Check if a customer was passed via route parameter
    $customerId = request()->route('customer');
    if ($customerId && !$this->customer_id) {
        $customer = Customer::where('id', $customerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer = $customer;
        }
    }

    // Check if a site was passed via query parameter (fallback)
    if (!$this->site_id) {
        $siteId = request()->query('site');
        if ($siteId) {
            $site = Site::where('id', $siteId)
                ->whereHas('customer', function ($query): void {
                    $query->where('user_id', auth()->id());
                })
                ->with('customer')
                ->first();

            if ($site) {
                $this->customer_id = $site->customer_id;
                $this->site_id = $site->id;
                $this->customer = $site->customer;
                $this->site = $site;
            }
        }
    }

    // Check if a customer was passed via query parameter (fallback)
    if (!$this->customer_id) {
        $customerId = request()->query('customer');
        if ($customerId) {
            $customer = Customer::where('id', $customerId)
                ->where('user_id', auth()->id())
                ->first();

            if ($customer) {
                $this->customer_id = $customer->id;
                $this->customer = $customer;
            }
        }
    }

    // Set default date to today if creating from dashboard
    if (!$this->scheduled_at) {
        $this->scheduled_at = now()->format('Y-m-d');
    }

    // Load sites for the selected customer
    if ($this->customer_id) {
        $this->loadSites();
    }
});

$loadSites = function (): void {
    if ($this->customer_id) {
        $this->sites = Site::where('customer_id', $this->customer_id)
            ->orderBy('address_line_1')
            ->get(['id', 'address_line_1', 'city'])
            ->toArray();
    } else {
        $this->sites = [];
    }
    $this->site_id = '';
};

$updated = function ($property): void {
    if ($property === 'customer_id') {
        $this->site_id = '';
        $this->loadSites();
    }
};

$save = function (): void {
    $this->validate();

    $scheduledDateTime = $this->scheduled_at . ' ' . $this->scheduled_time;

    Task::create([
        'site_id' => $this->site_id,
        'title' => $this->title,
        'description' => $this->description ?: null,
        'status' => $this->status,
        'scheduled_at' => $scheduledDateTime,
    ]);

    $this->redirect(route('dashboard'));
};

?>

<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Job</h1>

    <!-- Customer & Site Info Display (when pre-selected) -->
    @if($customer)
        <div class="rounded-lg border border-gray-200 bg-blue-50 p-4 dark:border-gray-600 dark:bg-blue-900/20">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <flux:icon name="user" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $customer->name }}</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $customer->phone }} @if($customer->email) • {{ $customer->email }} @endif</p>
                    </div>
                </div>
                @if($site)
                    <div class="flex items-center gap-3">
                        <flux:icon name="map-pin" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $site->address_line_1 }}</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">{{ $site->city }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <!-- Customer Selection (only show if no customer pre-selected) -->
            @if(!$customer)
                <div>
                    <flux:field label="Customer" required>
                        <select
                            wire:model.live="customer_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        >
                            <option value="">Select a customer</option>
                            @foreach($customers as $customerOption)
                                <option value="{{ $customerOption['id'] }}">{{ $customerOption['name'] }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </flux:field>
                </div>
            @endif

            <!-- Site Selection (only show if no site pre-selected) -->
            @if(!$site)
                <div>
                    <flux:field label="Service Site" required>
                        <select
                            wire:model="site_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            {{ count($sites) === 0 ? 'disabled' : '' }}
                        >
                            <option value="">Select a site</option>
                            @foreach($sites as $siteOption)
                                <option value="{{ $siteOption['id'] }}">{{ $siteOption['address_line_1'] }}, {{ $siteOption['city'] }}</option>
                            @endforeach
                        </select>
                        @if(count($sites) === 0 && $customer_id)
                            <p class="mt-1 text-sm text-amber-600 dark:text-amber-400">This customer has no sites. Please add a site first.</p>
                        @endif
                        @error('site_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </flux:field>
                </div>
            @endif

            <!-- Job Title -->
            <div>
                <flux:field label="Job Title" required>
                    <flux:input
                        wire:model="title"
                        placeholder="e.g., AC Maintenance, Filter Replacement"
                        error="{{ $errors->first('title') }}"
                    />
                </flux:field>
            </div>

            <!-- Job Description -->
            <div>
                <flux:field label="Description (Optional)">
                    <flux:textarea
                        wire:model="description"
                        placeholder="Enter job details, notes, or special instructions..."
                        rows="3"
                        error="{{ $errors->first('description') }}"
                    />
                </flux:field>
            </div>

            <!-- Status -->
            <div>
                <flux:field label="Status" required>
                    <select
                        wire:model="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                    >
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </flux:field>
            </div>

            <!-- Scheduled Date and Time -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <flux:field label="Scheduled Date" required>
                        <flux:input
                            type="date"
                            wire:model="scheduled_at"
                            error="{{ $errors->first('scheduled_at') }}"
                        />
                    </flux:field>
                </div>
                <div>
                    <flux:field label="Scheduled Time" required>
                        <flux:input
                            type="time"
                            wire:model="scheduled_time"
                            error="{{ $errors->first('scheduled_time') }}"
                        />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                @if($site)
                    <flux:button variant="outline" href="{{ route('sites.show', $site) }}" wire:navigate>
                        Cancel
                    </flux:button>
                @elseif($customer)
                    <flux:button variant="outline" href="{{ route('customers.show', $customer) }}" wire:navigate>
                        Cancel
                    </flux:button>
                @else
                    <flux:button variant="outline" href="{{ route('dashboard') }}" wire:navigate>
                        Cancel
                    </flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    Create Job
                </flux:button>
            </div>
        </form>
    </div>
</div>
