<?php

use App\Models\Customer;
use App\Models\Site;
use function Livewire\Volt\{state, mount};

state(['customer' => null, 'sites' => [], 'jobs' => [], 'jobSearch' => '', 'statusUpdate' => []]);

mount(function (): void {
    $customerId = request()->route('customer');

    // First check if customer exists
    $customer = Customer::find($customerId);
    if (!$customer) {
        abort(404);
    }

    // Then check if user is authorized to access this customer
    if ($customer->user_id !== auth()->id()) {
        abort(403);
    }

    $this->customer = $customer->load([
        'sites' => function($q): void { $q->withCount(['assets', 'tasks']); },
        'sites.assets',
        'sites.tasks' => function ($query): void { $query->orderBy('scheduled_at', 'desc'); }
    ]);

    $this->sites = $this->customer->sites;
    $this->jobs = $this->customer->tasks()
        ->with('site')
        ->when($this->jobSearch, function($q): void {
            $term = "%{$this->jobSearch}%";
            $q->where('title', 'like', $term)
              ->orWhere('description', 'like', $term)
              ->orWhereHas('site', fn($sq) => $sq->where('address_line_1', 'like', $term)->orWhere('city', 'like', $term));
        })
        ->orderBy('scheduled_at', 'desc')
        ->get();
});

$updateJobStatus = function (int $jobId, string $status): void {
    $job = $this->customer->tasks()->whereKey($jobId)->firstOrFail();
    $job->update(['status' => $status]);
    $this->redirect(request()->fullUrl());
};

?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customer->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Customer Details</p>
        </div>
        <div class="flex gap-2">
            <flux:button variant="outline" href="{{ route('customers.list') }}" wire:navigate>
                Back to Customers
            </flux:button>
                            <flux:button variant="primary" href="{{ route('tasks.create', ['customer' => $customer->id]) }}" wire:navigate>
                Create New Job
            </flux:button>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contact Information</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $customer->phone }}</p>
            </div>
            @if($customer->email)
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $customer->email }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sites -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Service Sites</h2>
                <flux:button variant="outline" size="sm" href="{{ route('sites.create.with-customer', $customer) }}" wire:navigate>
                    Add Site
                </flux:button>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if($sites->count() > 0)
                @foreach($sites as $site)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $site->address_line_1 }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $site->city }}</p>
                                @if($site->notes)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $site->notes }}</p>
                                @endif
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="cube" class="h-4 w-4" />
                                        {{ $site->assets_count ?? 0 }} asset{{ ($site->assets_count ?? 0) !== 1 ? 's' : '' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="clipboard-document-list" class="h-4 w-4" />
                                        {{ $site->tasks_count ?? 0 }} task{{ ($site->tasks_count ?? 0) !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:button variant="outline" size="sm" href="{{ route('sites.show', $site) }}" wire:navigate>
                                    View
                                </flux:button>
                                <flux:button variant="primary" size="sm" href="{{ route('tasks.create', ['site' => $site->id]) }}" wire:navigate>
                                    New Task
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="map-pin" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No sites yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a service site to get started.</p>
                    <div class="mt-6">
                        <flux:button variant="primary" href="{{ route('sites.create.with-customer', $customer) }}" wire:navigate>
                            Add Site
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Job History -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700 flex items-center justify-between gap-3">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Job History</h2>
            <div class="flex items-center gap-3">
                <flux:input placeholder="Search jobs..." wire:model.live.debounce.300ms="jobSearch" class="w-56" />
                <flux:button variant="outline" size="sm" href="{{ route('tasks.calendar') }}" wire:navigate>
                    Calendar
                </flux:button>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if($jobs->count() > 0)
                @foreach($jobs as $job)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $job->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $job->site->address_line_1 }}, {{ $job->site->city }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $job->scheduled_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select wire:change="updateJobStatus({{ $job->id }}, $event.target.value)" class="rounded border border-gray-300 bg-white px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700">
                                    <option value="scheduled" {{ $job->status->value === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="in_progress" {{ $job->status->value === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $job->status->value === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $job->status->value === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <flux:button variant="outline" size="sm" href="{{ route('tasks.edit', $job->slug) }}" wire:navigate>Edit</flux:button>
                                @if($job->status->value === 'completed' && !$job->invoice)
                                    <flux:button variant="primary" size="sm" href="{{ route('invoices.create', ['task' => $job->id]) }}" wire:navigate>
                                        Create Invoice
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or create a new job.</p>
                </div>
            @endif
        </div>
    </div>
</div>
