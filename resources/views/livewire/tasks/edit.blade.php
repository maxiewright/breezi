<?php

use App\Models\Customer;
use App\Models\Site;
use App\Models\Task;
use function Livewire\Volt\{state, rules, mount};

state([
    'job' => null,
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
    $taskId = request()->route('task');

    // Load the task
    $job = Task::whereHas('site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })->findOrFail($taskId);

    $this->job = $job;
    $this->customer_id = $job->site->customer_id;
    $this->site_id = $job->site_id;
    $this->title = $job->title;
    $this->description = $job->description;
    $this->status = $job->status->value;
    $this->scheduled_at = $job->scheduled_at->format('Y-m-d');
    $this->scheduled_time = $job->scheduled_at->format('H:i');

    // Load customer and site data
    $this->customer = $job->site->customer;
    $this->site = $job->site;

    // Load customers and sites
    $this->customers = Customer::where('user_id', auth()->id())
        ->orderBy('name')
        ->get(['id', 'name', 'phone', 'email'])
        ->toArray();

    $this->sites = Site::where('customer_id', $this->customer_id)
        ->orderBy('address_line_1')
        ->get(['id', 'address_line_1', 'city'])
        ->toArray();
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

    $this->job->update([
        'customer_id' => $this->customer_id,
        'site_id' => $this->site_id,
        'title' => $this->title,
        'description' => $this->description ?: null,
        'status' => $this->status,
        'scheduled_at' => $scheduledDateTime,
        'completed_at' => $this->status === 'completed' ? now() : null,
    ]);

    $this->redirect(route('tasks.list'));
};

$delete = function (): void {
    $this->job->delete();
    $this->redirect(route('tasks.list'));
};

?>

<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Job</h1>

    <!-- Customer & Site Info Display -->
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
            <!-- Customer Selection -->
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

            <!-- Site Selection -->
            <div>
                <flux:field label="Service Site" required>
                    <select
                        wire:model="site_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        {{ count($sites) === 0 ? 'disabled' : '' }}
                    >
                        <option value="">Select a site</option>
                        @foreach($sites as $siteOption)
                            <option value="{{ $siteOption['id'] }}">
                                {{ $siteOption['address_line_1'] }}, {{ $siteOption['city'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('site_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </flux:field>
            </div>

            <div>
                <flux:field label="Job Title" required>
                    <flux:input
                        wire:model="title"
                        placeholder="e.g., AC Maintenance, Repair, Installation"
                        error="{{ $errors->first('title') }}"
                    />
                </flux:field>
            </div>

            <div>
                <flux:field label="Description (Optional)">
                    <flux:textarea
                        wire:model="description"
                        placeholder="Provide additional details about the job..."
                        rows="3"
                        error="{{ $errors->first('description') }}"
                    />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <flux:field label="Status" required>
                        <select
                            wire:model="status"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
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
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <flux:field label="Date" required>
                        <flux:input
                            wire:model="scheduled_at"
                            type="date"
                            error="{{ $errors->first('scheduled_at') }}"
                        />
                    </flux:field>
                </div>
                <div>
                    <flux:field label="Time" required>
                        <flux:input
                            wire:model="scheduled_time"
                            type="time"
                            error="{{ $errors->first('scheduled_time') }}"
                        />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <flux:button variant="outline" href="{{ route('tasks.list') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button variant="danger" wire:click="delete" wire:confirm="Are you sure you want to delete this job?">
                    Delete Job
                </flux:button>
                <flux:button variant="primary" type="submit">
                    Update Job
                </flux:button>
            </div>
        </form>
    </div>
</div>

