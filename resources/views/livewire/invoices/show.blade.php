<?php

use App\Models\Invoice;
use function Livewire\Volt\{computed};

$invoice = computed(fn() => Invoice::where('id', request()->route('invoice'))
    ->with(['job.site.customer', 'items'])
    ->firstOrFail());

$shareInvoice = function (): void {
    // This will be handled by JavaScript to trigger the Web Share API
    $this->dispatch('share-invoice');
};

$downloadPdf = function (): void {
    // Generate and download PDF
    $this->dispatch('download-pdf');
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice {{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->job->site->customer->name }}</p>
        </div>
        <div class="flex gap-2">
            <flux:button variant="outline" href="{{ route('dashboard') }}" wire:navigate>
                Back to Dashboard
            </flux:button>
            <flux:button variant="outline" wire:click="downloadPdf">
                Download PDF
            </flux:button>
            <flux:button variant="primary" wire:click="shareInvoice">
                Share Invoice
            </flux:button>
        </div>
    </div>

    <!-- Invoice Status -->
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Invoice Status</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current status: {{ ucfirst($invoice->status) }}</p>
            </div>
            <flux:badge variant="{{ $invoice->status === 'paid' ? 'primary' : ($invoice->status === 'sent' ? 'secondary' : 'outline') }}">
                {{ ucfirst($invoice->status) }}
            </flux:badge>
        </div>
    </div>

    <!-- Customer and Job Information -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer & Job Information</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer Name</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->site->customer->name }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->site->customer->phone }}</p>
            </div>
            @if($invoice->job->site->customer->email)
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->site->customer->email }}</p>
                </div>
            @endif
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Address</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->site->address_line_1 }}, {{ $invoice->job->site->city }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Title</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->title }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Date</label>
                <p class="text-sm text-gray-900 dark:text-white">{{ $invoice->job->scheduled_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Invoice Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex justify-end">
                <div class="text-right">
                    <p class="text-lg font-medium text-gray-900 dark:text-white">Total: ${{ number_format($invoice->total_amount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->notes)
        <!-- Notes -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notes</h2>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $invoice->notes }}</p>
        </div>
    @endif

    <!-- Actions -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Invoice Actions</h2>
        <div class="flex flex-wrap gap-3">
            <flux:button variant="outline" wire:click="downloadPdf">
                <flux:icon name="arrow-down-tray" class="h-4 w-4 mr-2" />
                Download PDF
            </flux:button>
            <flux:button variant="primary" wire:click="shareInvoice">
                <flux:icon name="share" class="h-4 w-4 mr-2" />
                Share Invoice
            </flux:button>
            @if($invoice->status === 'draft')
                <flux:button variant="secondary" href="{{ route('invoices.edit', $invoice) }}" wire:navigate>
                    <flux:icon name="pencil" class="h-4 w-4 mr-2" />
                    Edit Invoice
                </flux:button>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('share-invoice', () => {
        if (navigator.share) {
            navigator.share({
                title: 'Invoice {{ $invoice->invoice_number }}',
                text: 'Invoice for {{ $invoice->job->site->customer->name }} - {{ $invoice->job->title }}',
                url: window.location.href
            }).catch(console.error);
        } else {
            // Fallback for browsers that don't support Web Share API
            alert('Sharing not supported in this browser. You can copy the URL manually.');
        }
    });

    Livewire.on('download-pdf', () => {
        // This would typically generate and download a PDF
        // For now, we'll just show an alert
        alert('PDF download functionality would be implemented here using spatie/laravel-pdf');
    });
});
</script>
