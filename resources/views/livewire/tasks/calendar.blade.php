<?php

use App\Models\Task;
use function Livewire\Volt\{state, mount};

state([
    'currentDate' => null,
    'selectedDate' => null,
    'jobs' => [],
]);

mount(function (): void {
    $this->currentDate = now();
    $this->selectedDate = now();
    $this->loadTasks();
});

$loadTasks = function (): void {
    $startOfMonth = $this->currentDate->copy()->startOfMonth();
    $endOfMonth = $this->currentDate->copy()->endOfMonth();

    $this->jobs = Task::whereHas('site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
    ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
    ->with(['site.customer'])
    ->orderBy('scheduled_at')
    ->get();
};

$previousMonth = function (): void {
    $this->currentDate = $this->currentDate->subMonth();
    $this->loadTasks();
};

$nextMonth = function (): void {
    $this->currentDate = $this->currentDate->addMonth();
    $this->loadTasks();
};

$goToToday = function (): void {
    $this->currentDate = now();
    $this->selectedDate = now();
    $this->loadTasks();
};

$selectDate = function ($date): void {
    $this->selectedDate = \Illuminate\Support\Carbon::parse($date);
};

$createTaskForDate = (fn($date) => $this->redirect(route('tasks.create', ['date' => $date])));

?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Job Calendar</h1>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" size="sm" wire:click="goToToday">
                    Today
                </flux:button>
                <flux:button variant="outline" size="sm" wire:click="previousMonth">
                    Previous
                </flux:button>
                <flux:button variant="outline" size="sm" wire:click="nextMonth">
                    Next
                </flux:button>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:button variant="outline" href="{{ route('tasks.list') }}" wire:navigate>
                List View
            </flux:button>
            <flux:button variant="primary" href="{{ route('tasks.create') }}" wire:navigate>
                Create Task
            </flux:button>
        </div>
    </div>

    <!-- Month/Year Display -->
    <div class="text-center">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $this->currentDate->format('F Y') }}
        </h2>
    </div>

    <!-- Simple Calendar Grid -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                <div class="p-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $weekday }}
                </div>
            @endforeach
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7">
            @for($week = 0; $week < 6; $week++)
                @for($day = 0; $day < 7; $day++)
                    @php
                        $date = $this->currentDate->copy()->startOfMonth()->startOfWeek()->addDays($week * 7 + $day);
                        $isCurrentMonth = $date->month === $this->currentDate->month;
                        $isToday = $date->isToday();
                        $dateString = $date->format('Y-m-d');
                        $dayJobs = $this->jobs->filter(function($job) use ($dateString) {
                            return $job->scheduled_at->format('Y-m-d') === $dateString;
                        });
                    @endphp

                    <div class="min-h-[120px] border-r border-b border-gray-200 dark:border-gray-700 p-2 {{ $isCurrentMonth ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                        <!-- Date Number -->
                        <div class="flex items-center justify-between mb-2">
                            <button wire:click="createTaskForDate('{{ $dateString }}')" class="text-sm font-medium {{ $isCurrentMonth ? 'text-gray-900 dark:text-white hover:text-blue-600' : 'text-gray-400 dark:text-gray-500' }} {{ $isToday ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }} hover:underline transition-colors" title="Click to add task on {{ $date->format('M j, Y') }}">
                                {{ $date->day }}
                            </button>
                            @if($dayJobs->count() > 0)
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium text-white bg-blue-600 rounded-full">
                                    {{ $dayJobs->count() }}
                                </span>
                            @endif
                        </div>

                        <!-- Jobs for this day -->
                        <div class="space-y-1">
                            @foreach($dayJobs->take(2) as $job)
                                <a href="{{ route('tasks.edit', $job->slug) }}" wire:navigate class="block text-xs p-1 rounded cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border border-transparent hover:border-blue-200 dark:hover:border-blue-800"
                                     title="Click to view/edit: {{ $job->title }} - {{ $job->site->customer->name }}">
                                    <div class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ $job->title }}
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400 truncate">
                                        {{ $job->site->customer->name }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $job->scheduled_at->format('g:i A') }}
                                    </div>
                                </a>
                            @endforeach
                            @if($dayJobs->count() > 2)
                                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                    +{{ $dayJobs->count() - 2 }} more
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            @endfor
        </div>
    </div>

    <!-- Selected Date Details -->
    @if($this->selectedDate)
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Jobs for {{ $this->selectedDate->format('l, F j, Y') }}
                </h3>
                <flux:button variant="primary" size="sm" href="{{ route('tasks.create', ['date' => $this->selectedDate->format('Y-m-d')]) }}" wire:navigate>
                    Create Task on this date
                </flux:button>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @php
                    $selectedDateJobs = $this->jobs->filter(function($job) {
                        return $job->scheduled_at->format('Y-m-d') === $this->selectedDate->format('Y-m-d');
                    });
                @endphp

                @if($selectedDateJobs->count() > 0)
                    @foreach($selectedDateJobs as $job)
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                            <a href="{{ route('tasks.edit', $job->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $job->title }}
                                            </a>
                                        </h4>
                                        <flux:badge variant="{{ $job->status->value === 'completed' ? 'success' : ($job->status->value === 'cancelled' ? 'danger' : 'outline') }}">
                                            {{ $job->status->label() }}
                                        </flux:badge>
                                    </div>

                                    @if($job->description)
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $job->description }}</p>
                                    @endif

                                    <div class="mt-3 flex items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="user" class="h-4 w-4" />
                                            <a href="{{ route('customers.show', $job->site->customer->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $job->site->customer->name }}
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="map-pin" class="h-4 w-4" />
                                            <a href="{{ route('sites.show', $job->site->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $job->site->address_line_1 }}, {{ $job->site->city }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-6 flex items-center gap-2">
                                    <flux:button variant="outline" size="sm" href="{{ route('tasks.edit', $job->slug) }}" wire:navigate>
                                        Edit
                                    </flux:button>
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
                        <flux:icon name="calendar" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs scheduled</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new job for this date.</p>
                        <div class="mt-6">
                            <flux:button variant="primary" href="{{ route('tasks.create', ['date' => $this->selectedDate->format('Y-m-d')]) }}" wire:navigate>
                                Create Task on this date
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
