<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use function Livewire\Volt\{state, rules, computed, mount};

state([
    'invoice_id' => '',
    'notes' => '',
    'items' => [],
]);

rules([
    'notes' => 'nullable|string|max:1000',
    'items.*.description' => 'required|string|max:255',
    'items.*.quantity' => 'required|integer|min:1',
    'items.*.unit_price' => 'required|numeric|min:0',
    'items.*.total_price' => 'required|numeric|min:0',
]);

mount(function (): void {
    $invoiceId = request()->route('invoice');
    $invoice = Invoice::where('id', $invoiceId)
        ->whereHas('job.site.customer', function ($query): void {
            $query->where('user_id', auth()->id());
        })
        ->with('items')
        ->firstOrFail();

    $this->invoice_id = $invoice->id;
    $this->notes = $invoice->notes ?? '';
    $this->items = $invoice->items->map(fn($item): array => [
        'id' => $item->id,
        'description' => $item->description,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'total_price' => $item->total_price,
    ])->toArray();
});

$invoice = computed(fn() => Invoice::where('id', $this->invoice_id)
    ->whereHas('job.site.customer', function ($query): void {
        $query->where('user_id', auth()->id());
    })
    ->with(['job.site.customer', 'items'])
    ->first());

$totalAmount = computed(fn() => collect($this->items)->sum('total_price'));

$addItem = function (): void {
    $this->items[] = [
        'id' => null,
        'description' => '',
        'quantity' => 1,
        'unit_price' => '',
        'total_price' => '',
    ];
};

$removeItem = function ($index): void {
    unset($this->items[$index]);
    $this->items = array_values($this->items);
};

$updateItemTotal = function ($index): void {
    if (isset($this->items[$index]['quantity']) && isset($this->items[$index]['unit_price'])) {
        $quantity = (int) $this->items[$index]['quantity'];
        $unitPrice = (float) $this->items[$index]['unit_price'];
        $this->items[$index]['total_price'] = number_format($quantity * $unitPrice, 2);
    }
};

$save = function (): void {
    $this->validate();

    $invoice = Invoice::find($this->invoice_id);
    $invoice->update([
        'notes' => $this->notes,
        'total_amount' => $this->totalAmount,
    ]);

    // Update existing items and create new ones
    foreach ($this->items as $itemData) {
        if (isset($itemData['id']) && $itemData['id']) {
            // Update existing item
            InvoiceItem::where('id', $itemData['id'])->update([
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
            ]);
        } else {
            // Create new item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
            ]);
        }
    }

    $this->redirect(route('invoices.show', $invoice->id));
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Invoice</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Invoice #{{ $invoice?->invoice_number }} - {{ $invoice?->job?->site?->customer?->name }}
            </p>
        </div>
        <flux:button variant="outline" href="{{ route('invoices.show', $this->invoice_id) }}" wire:navigate>
            Back to Invoice
        </flux:button>
    </div>

    <!-- Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <!-- Invoice Details -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <flux:field label="Invoice Number">
                        <flux:input value="{{ $invoice?->invoice_number }}" disabled />
                    </flux:field>
                </div>
                <div>
                    <flux:field label="Status">
                        <flux:input value="{{ ucfirst($invoice?->status) }}" disabled />
                    </flux:field>
                </div>
            </div>

            <div>
                <flux:field label="Notes">
                    <flux:textarea
                        wire:model="notes"
                        placeholder="Add any additional notes..."
                        rows="3"
                        error="{{ $errors->first('notes') }}"
                    />
                </flux:field>
            </div>

            <!-- Invoice Items -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Invoice Items</h3>
                    <flux:button type="button" variant="outline" size="sm" wire:click="addItem">
                        Add Item
                    </flux:button>
                </div>

                <div class="space-y-4">
                    @foreach($items as $index => $item)
                        <div class="grid grid-cols-12 gap-4 items-end border border-gray-200 rounded-lg p-4 dark:border-gray-700">
                            <div class="col-span-6">
                                <flux:field label="Description" required>
                                    <flux:input
                                        wire:model="items.{{ $index }}.description"
                                        placeholder="Item description"
                                        error="{{ $errors->first("items.{$index}.description") }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-2">
                                <flux:field label="Qty" required>
                                    <flux:input
                                        wire:model="items.{{ $index }}.quantity"
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
                                        wire:model="items.{{ $index }}.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        wire:change="updateItemTotal({{ $index }})"
                                        error="{{ $errors->first("items.{$index}.unit_price") }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-1">
                                <flux:field label="Total">
                                    <flux:input
                                        wire:model="items.{{ $index }}.total_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        disabled
                                    />
                                </flux:field>
                            </div>
                            <div class="col-span-1">
                                @if(count($items) > 1)
                                    <flux:button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        wire:click="removeItem({{ $index }})"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Remove
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Total -->
            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                <div class="flex justify-end">
                    <div class="text-right">
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            Total: ${{ number_format($totalAmount, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <flux:button variant="outline" href="{{ route('invoices.show', $this->invoice_id) }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Update Invoice
                </flux:button>
            </div>
        </form>
    </div>
</div>
