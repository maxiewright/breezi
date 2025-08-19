<?php

use App\Models\Task;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use function Livewire\Volt\{state, rules, computed};

state([
    'task_id' => '',
    'invoice_number' => '',
    'notes' => '',
    'items' => [
        ['description' => '', 'quantity' => 1, 'unit_price' => '', 'total_price' => '']
    ],
]);

rules([
    'task_id' => 'required|exists:tasks,id',
    'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
    'notes' => 'nullable|string',
    'items.*.description' => 'required|string|max:255',
    'items.*.quantity' => 'required|integer|min:1',
    'items.*.unit_price' => 'required|numeric|min:0',
]);

$task = computed(function () {
    if (!$this->task_id) {
        return null;
    }

    return Task::where('id', $this->task_id)
        ->with(['site.customer'])
        ->first();
});

$totalAmount = computed(fn() => collect($this->items)->sum(fn($item): int|float => ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)));

$addItem = function (): void {
    $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => '', 'total_price' => ''];
};

$removeItem = function ($index): void {
    unset($this->items[$index]);
    $this->items = array_values($this->items);
};

$updateItemTotal = function ($index): void {
    $item = $this->items[$index];
    $quantity = intval($item['quantity'] ?? 0);
    $unitPrice = floatval($item['unit_price'] ?? 0);
    $this->items[$index]['total_price'] = $quantity * $unitPrice;
};

$save = function (): void {
    $this->validate();

    // Create invoice
    $invoice = Invoice::create([
        'task_id' => $this->task_id,
        'number' => $this->invoice_number,
        'status' => 'draft',
        'total' => $this->totalAmount,
        'notes' => $this->notes ?: null,
    ]);

    // Create invoice items
    foreach ($this->items as $item) {
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['quantity'] * $item['unit_price'],
        ]);
    }

    // Generate PDF and redirect to share
    $this->redirect(route('invoices.show', $invoice));
};

$generateInvoiceNumber = function (): void {
    $lastInvoice = Invoice::orderBy('id', 'desc')->first();
    $nextNumber = $lastInvoice ? intval(substr((string) $lastInvoice->invoice_number, 4)) + 1 : 1;
    $this->invoice_number = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Invoice</h1>
        <flux:button variant="outline" href="{{ route('dashboard') }}" wire:navigate>
            Back to Dashboard
        </flux:button>
    </div>

    @if($task)
        <!-- Job Information -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Job Information</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $task->site->customer->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Site</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $task->site->address_line_1 }}, {{ $task->site->city }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Task Title</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $task->title }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled Date</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $task->scheduled_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Invoice Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <!-- Invoice Number -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <flux:field label="Invoice Number" required>
                        <div class="flex gap-2">
                            <flux:input
                                wire:model="invoice_number"
                                placeholder="INV-0001"
                                error="{{ $errors->first('invoice_number') }}"
                            />
                            <flux:button type="button" variant="outline" wire:click="generateInvoiceNumber">
                                Generate
                            </flux:button>
                        </div>
                    </flux:field>
                </div>
                <div>
                    <flux:field label="Total Amount">
                        <flux:input
                            value="${{ number_format($totalAmount, 2) }}"
                            disabled
                            class="bg-gray-50"
                        />
                    </flux:field>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <flux:field label="Notes (Optional)">
                    <flux:textarea
                        wire:model="notes"
                        placeholder="Enter any additional notes for the invoice"
                        rows="3"
                        error="{{ $errors->first('notes') }}"
                    />
                </flux:field>
            </div>

            <!-- Invoice Items -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Invoice Items</h3>
                    <flux:button type="button" variant="outline" wire:click="addItem">
                        Add Item
                    </flux:button>
                </div>

                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-12 gap-4 items-end border border-gray-200 rounded-lg p-4 dark:border-gray-700">
                            <div class="col-span-5">
                                <flux:field label="Description" required>
                                    <flux:input
                                        wire:model="items.{{ $index }}.description"
                                        placeholder="Service description"
                                        error="{{ $errors->first("items.{$index}.description") }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-2">
                                <flux:field label="Qty" required>
                                    <flux:input
                                        wire:model.live="items.{{ $index }}.quantity"
                                        type="number"
                                        min="1"
                                        wire:change="updateItemTotal({{ $index }})"
                                        error="{{ $errors->first("items.{$index}.quantity") }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-2">
                                <flux:field label="Unit Price" required>
                                    <flux:input
                                        wire:model.live="items.{{ $index }}.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        wire:change="updateItemTotal({{ $index }})"
                                        error="{{ $errors->first("items.{$index}.unit_price") }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-2">
                                <flux:field label="Total">
                                    <flux:input
                                        value="${{ number_format($item['total_price'] ?? 0, 2) }}"
                                        disabled
                                        class="bg-gray-50"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-1">
                                @if(count($items) > 1)
                                    <flux:button type="button" variant="ghost" size="sm" wire:click="removeItem({{ $index }})" class="text-red-600">
                                        <flux:icon name="trash" class="h-4 w-4" />
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <flux:button variant="outline" href="{{ route('dashboard') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Invoice
                </flux:button>
            </div>
        </form>
    </div>
</div>
