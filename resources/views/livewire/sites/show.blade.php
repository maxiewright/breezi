<?php

use App\Models\Site;
use function Livewire\Volt\{state, mount};

state(['site' => null]);

mount(function (): void {
    $siteSlug = request()->route('site');
    $this->site = Site::where('slug', $siteSlug)
        ->whereHas('customer', function ($query): void {
            $query->where('user_id', auth()->id());
        })
        ->withCount(['assets', 'tasks'])
        ->with(['customer', 'assets', 'tasks' => function ($query): void {
            $query->orderBy('scheduled_at', 'desc');
        }])
        ->firstOrFail();
});

?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $site->address_line_1 }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $site->city }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Customer: {{ $site->customer->name }}</p>
            <div class="mt-1 flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                <span class="flex items-center gap-1"><flux:icon name="cube" class="h-4 w-4" />{{ $site->assets_count }} assets</span>
                <span class="flex items-center gap-1"><flux:icon name="clipboard-document-list" class="h-4 w-4" />{{ $site->tasks_count }} tasks</span>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:button variant="outline" href="{{ route('customers.show', $site->customer) }}" wire:navigate>
                Back to Customer
            </flux:button>
            <flux:button variant="primary" href="{{ route('jobs.create', ['site' => $site->id]) }}" wire:navigate>
                Create New Job
            </flux:button>
        </div>
    </div>

    <!-- Site Info -->
    @if($site->notes)
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notes</h2>
            <p class="text-sm text-gray-900 dark:text-white">{{ $site->notes }}</p>
        </div>
    @endif

    <!-- Assets -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Assets</h2>
                <flux:button variant="outline" size="sm" href="{{ route('assets.create.with-site', $site) }}" wire:navigate>
                    Add Asset
                </flux:button>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if($site->assets->count() > 0)
                @foreach($site->assets as $asset)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $asset->name }}</h3>
                                @if($asset->assetBrand || $asset->assetModel)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $asset->assetBrand?->name ?? '' }} {{ $asset->assetModel?->name ?? '' }}
                                    </p>
                                @endif
                                @if($asset->installed_on)
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Installed: {{ $asset->installed_on->format('M j, Y') }}
                                    </p>
                                @endif
                                @if($asset->serial_number)
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Serial: {{ $asset->serial_number }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="cube" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No assets yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add assets to track equipment at this site.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Job History -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Job History</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if($site->tasks->count() > 0)
                @foreach($site->tasks as $job)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $job->title }}</h3>
                                @if($job->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $job->description }}</p>
                                @endif
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create your first job for this site.</p>
                    <div class="mt-6">
                        <flux:button variant="primary" href="{{ route('tasks.create', ['site' => $site->id]) }}" wire:navigate>
                            Create Task
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
