<?php

use App\Models\Task;
use App\Models\Invoice;
use function Livewire\Volt\{state, mount};

state([
    'search' => '',
    'todayJobs' => [],
    'monthlySales' => 0,
    'yearlySales' => 0,
    'pendingJobs' => [],
]);

mount(function (): void {
    $this->loadDashboardData();
});

$loadDashboardData = function (): void {
    $this->todayJobs = Task::whereHas('site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
        ->whereDate('scheduled_at', today())
        ->where('status', 'scheduled')
        ->with(['site.customer'])
        ->orderBy('scheduled_at')
        ->get()
        ->map(fn($job): array => [
            'id' => $job->id,
            'title' => $job->title,
            'status' => $job->status->value,
            'status_label' => $job->status->label(),
            'scheduled_at' => $job->scheduled_at,
            'site' => [
                'address_line_1' => $job->site->address_line_1,
                'city' => $job->site->city,
                'customer' => [
                    'name' => $job->site->customer->name,
                ],
            ],
        ])
        ->toArray();

    $this->monthlySales = Invoice::whereHas('task.site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
    ->where('status', 'paid')
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->sum('total');

    $this->yearlySales = Invoice::whereHas('task.site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
    ->where('status', 'paid')
    ->whereYear('created_at', now()->year)
    ->sum('total');

    $this->pendingJobs = Task::whereHas('site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
        ->whereIn('status', ['scheduled', 'in_progress'])
        ->with(['site.customer'])
        ->orderBy('scheduled_at')
        ->limit(5)
        ->get()
        ->map(fn($job): array => [
            'id' => $job->id,
            'title' => $job->title,
            'status' => $job->status->value,
            'status_label' => $job->status->label(),
            'scheduled_at' => $job->scheduled_at,
            'invoice' => $job->invoice,
            'site' => [
                'address_line_1' => $job->site->address_line_1,
                'city' => $job->site->city,
                'customer' => [
                    'name' => $job->site->customer->name,
                ],
            ],
        ])
        ->toArray();
};

?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <div class="flex gap-2">
            <flux:button variant="outline" href="{{ route('tasks.calendar') }}" wire:navigate>
                Calendar View
            </flux:button>
            <flux:button variant="primary" href="{{ route('customers.create') }}" wire:navigate>
                Add Customer
            </flux:button>
            <flux:button variant="outline" href="{{ route('tasks.create') }}" wire:navigate>
                New Task
            </flux:button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <flux:icon name="calendar" class="h-8 w-8 text-blue-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Jobs</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ count($todayJobs) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <flux:icon name="currency-dollar" class="h-8 w-8 text-green-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Sales</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($monthlySales, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <flux:icon name="chart-bar" class="h-8 w-8 text-purple-600" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Yearly Sales</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($yearlySales, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Jobs -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Today's Scheduled Jobs</h2>
                <flux:button variant="outline" size="sm" href="{{ route('tasks.calendar') }}" wire:navigate>
                    View Calendar
                </flux:button>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if(count($todayJobs) > 0)
                @foreach($todayJobs as $job)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $job['title'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $job['site']['customer']['name'] }} - {{ $job['site']['address_line_1'] }}, {{ $job['site']['city'] }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $job['scheduled_at']->format('g:i A') }}
                                </p>
                            </div>
                            <flux:badge variant="{{ $job['status'] === 'scheduled' ? 'outline' : 'primary' }}">
                                {{ $job['status_label'] }}
                            </flux:badge>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="calendar" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs scheduled today</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new job.</p>
                    <div class="mt-6">
                        <flux:button variant="primary" href="{{ route('tasks.create') }}" wire:navigate>
                            Create Job
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Jobs (Next 7 Days) -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Upcoming Jobs (Next 7 Days)</h2>
                <flux:button variant="outline" size="sm" href="{{ route('tasks.calendar') }}" wire:navigate>
                    View Calendar
                </flux:button>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @php
                $upcomingJobs = \App\Models\Task::whereHas('site.customer', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->where('status', 'scheduled')
                ->whereBetween('scheduled_at', [now()->addDay(), now()->addDays(7)])
                ->with(['site.customer'])
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get();
            @endphp

            @if($upcomingJobs->count() > 0)
                @foreach($upcomingJobs as $job)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $job->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $job->site->customer->name }} - {{ $job->site->address_line_1 }}, {{ $job->site->city }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $job->scheduled_at->format('D, M j, g:i A') }}
                                </p>
                            </div>
                            <flux:badge variant="outline">
                                {{ $job->scheduled_at->diffForHumans() }}
                            </flux:badge>
                        </div>
                    </div>
                @endforeach
                @if($upcomingJobs->count() >= 5)
                    <div class="px-6 py-4 text-center">
                        <flux:button variant="outline" size="sm" href="{{ route('tasks.calendar') }}" wire:navigate>
                            View All Upcoming Jobs
                        </flux:button>
                    </div>
                @endif
            @else
                <div class="px-6 py-8 text-center">
                    <flux:icon name="calendar-days" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No upcoming jobs</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Schedule some jobs for the coming week.</p>
                    <div class="mt-6">
                        <flux:button variant="primary" href="{{ route('tasks.create') }}" wire:navigate>
                            Create Job
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Jobs -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recent Jobs</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @if(count($pendingJobs) > 0)
                @foreach($pendingJobs as $job)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $job['title'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $job['site']['customer']['name'] }} - {{ $job['site']['address_line_1'] }}, {{ $job['site']['city'] }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $job['scheduled_at']->format('M j, g:i A') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge variant="{{ $job['status'] === 'scheduled' ? 'outline' : 'primary' }}">
                                    {{ $job['status_label'] }}
                                </flux:badge>
                                @if($job['status'] === 'completed' && !$job['invoice'])
                                    <flux:button variant="primary" size="sm" href="{{ route('invoices.create', ['task' => $job['id']]) }}" wire:navigate>
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent jobs</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create your first job to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>
