<?php

use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetModel;
use App\Models\Site;
use function Livewire\Volt\{state, rules, mount, computed};

state([
    'site_id' => '',
    'asset_brand_id' => '',
    'asset_model_id' => '',
    'name' => '',
    'installed_on' => '',
    'serial_number' => '',
    'site' => null,

    // For creating new brand/model on the fly
    'showNewBrandForm' => false,
    'showNewModelForm' => false,
    'newBrandName' => '',
    'newBrandDescription' => '',
    'newModelName' => '',
    'newModelDescription' => '',
    'newModelNumber' => '',
    'newBtuRating' => '',
    'newEfficiencyRating' => '',
]);

rules([
    'site_id' => 'required|exists:sites,id',
    'asset_brand_id' => 'required|exists:asset_brands,id',
    'asset_model_id' => 'required|exists:asset_models,id',
    'name' => 'required|string|max:255',
    'installed_on' => 'nullable|date',
    'serial_number' => 'nullable|string|max:255',

    // New brand/model validation
    'newBrandName' => 'required_if:showNewBrandForm,true|string|max:255|unique:asset_brands,name',
    'newBrandDescription' => 'nullable|string',
    'newModelName' => 'required_if:showNewModelForm,true|string|max:255',
    'newModelDescription' => 'nullable|string',
    'newModelNumber' => 'nullable|string|max:255',
    'newBtuRating' => 'nullable|numeric|min:0',
    'newEfficiencyRating' => 'nullable|string|max:255',
]);

$brands = computed(fn() => AssetBrand::active()->orderBy('name')->get());

$models = computed(function () {
    if (!$this->asset_brand_id) {
        return collect();
    }

    return AssetModel::where('asset_brand_id', $this->asset_brand_id)
        ->active()
        ->orderBy('name')
        ->get();
});

mount(function (): void {
    // Check if a site was passed via route parameter
    $siteId = request()->route('site');
    if ($siteId) {
        $this->site_id = $siteId;
    }

    // Check if a site was passed via query parameter (fallback)
    if (!$this->site_id) {
        $siteId = request()->query('site');
        if ($siteId) {
            $this->site_id = $siteId;
        }
    }

    // Load site data if site_id is set
    if ($this->site_id) {
        $this->site = Site::where('id', $this->site_id)
            ->whereHas('customer', function ($query): void {
                $query->where('user_id', auth()->id());
            })
            ->with('customer')
            ->first();
    }
});

$updatedAssetBrandId = function (): void {
    $this->asset_model_id = '';
    $this->showNewModelForm = false;
};

$createNewBrand = function (): void {
    $this->validate([
        'newBrandName' => 'required|string|max:255|unique:asset_brands,name',
        'newBrandDescription' => 'nullable|string',
    ]);

    $brand = AssetBrand::create([
        'name' => $this->newBrandName,
        'description' => $this->newBrandDescription,
        'is_active' => true,
    ]);

    $this->asset_brand_id = $brand->id;
    $this->asset_model_id = '';
    $this->showNewBrandForm = false;
    $this->newBrandName = '';
    $this->newBrandDescription = '';

    session()->flash('message', 'Brand created successfully!');
};

$createNewModel = function (): void {
    $this->validate([
        'asset_brand_id' => 'required|exists:asset_brands,id',
        'newModelName' => 'required|string|max:255',
        'newModelDescription' => 'nullable|string',
        'newModelNumber' => 'nullable|string|max:255',
        'newBtuRating' => 'nullable|numeric|min:0',
        'newEfficiencyRating' => 'nullable|string|max:255',
    ]);

    $model = AssetModel::create([
        'asset_brand_id' => $this->asset_brand_id,
        'name' => $this->newModelName,
        'description' => $this->newModelDescription,
        'model_number' => $this->newModelNumber,
        'btu_rating' => $this->newBtuRating ?: null,
        'efficiency_rating' => $this->newEfficiencyRating ?: null,
        'is_active' => true,
    ]);

    $this->asset_model_id = $model->id;
    $this->showNewModelForm = false;
    $this->newModelName = '';
    $this->newModelDescription = '';
    $this->newModelNumber = '';
    $this->newBtuRating = '';
    $this->newEfficiencyRating = '';

    session()->flash('message', 'Model created successfully!');
};

$save = function () {
    $this->validate();

    Asset::create([
        'site_id' => $this->site_id,
        'asset_brand_id' => $this->asset_brand_id,
        'asset_model_id' => $this->asset_model_id,
        'name' => $this->name,
        'installed_on' => $this->installed_on ?: null,
        'serial_number' => $this->serial_number ?: null,
    ]);

    if ($this->site) {
        return $this->redirect(route('sites.show', $this->site->slug));
    }

    return $this->redirect(route('customers.list'));
};

?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Asset</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add a new HVAC asset to track</p>
        </div>
        <flux:button variant="outline" href="{{ $site ? route('sites.show', $site->slug) : route('customers.list') }}" wire:navigate>
            Cancel
        </flux:button>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex">
                <flux:icon name="check-circle" class="h-5 w-5 text-green-400" />
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Pre-filled Site Info (if applicable) -->
    @if($site)
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-center gap-2 mb-2">
                <flux:icon name="information-circle" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Adding Asset For:</h3>
            </div>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Customer:</strong> {{ $site->customer->name }}<br>
                <strong>Site:</strong> {{ $site->address_line_1 }}, {{ $site->city }}
            </div>
        </div>
    @endif

    <!-- Asset Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <form wire:submit="save" class="space-y-6">
            <!-- Site Selection (if not pre-filled) -->
            @if(!$site)
                <div>
                    <flux:field label="Site" required>
                        <select wire:model.live="site_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Select a site</option>
                            @foreach(auth()->user()->customers()->with('sites')->get() as $customer)
                                <optgroup label="{{ $customer->name }}">
                                    @foreach($customer->sites as $siteOption)
                                        <option value="{{ $siteOption->id }}">
                                            {{ $siteOption->address_line_1 }}, {{ $siteOption->city }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('site_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </flux:field>
                </div>
            @endif

            <!-- Asset Name -->
            <div>
                <flux:field label="Asset Name" required>
                    <flux:input
                        wire:model="name"
                        placeholder="e.g., Main Unit, Upstairs Unit, Office AC"
                        error="{{ $errors->first('name') }}"
                    />
                </flux:field>
            </div>

            <!-- Brand Selection with Add New Option -->
            <div>
                <flux:field label="Brand" required>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <select wire:model.live="asset_brand_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Select a brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_brand_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <flux:button type="button" variant="outline" wire:click="$set('showNewBrandForm', true)">
                            Add New
                        </flux:button>
                    </div>
                </flux:field>
            </div>

            <!-- New Brand Form (Collapsible) -->
            @if($showNewBrandForm)
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200 mb-3">Add New Brand</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <flux:field label="Brand Name" required>
                                <flux:input
                                    wire:model="newBrandName"
                                    placeholder="e.g., Carrier, Trane"
                                    error="{{ $errors->first('newBrandName') }}"
                                />
                            </flux:field>
                        </div>
                        <div>
                            <flux:field label="Description">
                                <flux:input
                                    wire:model="newBrandDescription"
                                    placeholder="Brief description of the brand"
                                    error="{{ $errors->first('newBrandDescription') }}"
                                />
                            </flux:field>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <flux:button type="button" variant="primary" wire:click="createNewBrand">
                            Create Brand
                        </flux:button>
                        <flux:button type="button" variant="outline" wire:click="$set('showNewBrandForm', false)">
                            Cancel
                        </flux:button>
                    </div>
                </div>
            @endif

            <!-- Model Selection with Add New Option -->
            @if($asset_brand_id)
                <div>
                    <flux:field label="Model" required>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select wire:model="asset_model_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Select a model</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}">
                                            {{ $model->name }}
                                            @if($model->model_number) ({{ $model->model_number }}) @endif
                                            @if($model->btu_rating) - {{ number_format($model->btu_rating) }} BTU @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_model_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <flux:button type="button" variant="outline" wire:click="$set('showNewModelForm', true)">
                                Add New
                            </flux:button>
                        </div>
                    </flux:field>
                </div>

                <!-- New Model Form (Collapsible) -->
                @if($showNewModelForm)
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200 mb-3">Add New Model</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <flux:field label="Model Name" required>
                                    <flux:input
                                        wire:model="newModelName"
                                        placeholder="e.g., Infinity 21, XV20i"
                                        error="{{ $errors->first('newModelName') }}"
                                    />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field label="Model Number">
                                    <flux:input
                                        wire:model="newModelNumber"
                                        placeholder="e.g., 25VNA1, 4TTV0"
                                        error="{{ $errors->first('newModelNumber') }}"
                                    />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field label="BTU Rating">
                                    <flux:input
                                        wire:model="newBtuRating"
                                        type="number"
                                        placeholder="e.g., 24000"
                                        error="{{ $errors->first('newBtuRating') }}"
                                    />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field label="Efficiency Rating">
                                    <flux:input
                                        wire:model="newEfficiencyRating"
                                        placeholder="e.g., 16 SEER, 96% AFUE"
                                        error="{{ $errors->first('newEfficiencyRating') }}"
                                    />
                                </flux:field>
                            </div>
                            <div class="sm:col-span-2">
                                <flux:field label="Description">
                                    <flux:textarea
                                        wire:model="newModelDescription"
                                        placeholder="Brief description of this model"
                                        rows="2"
                                        error="{{ $errors->first('newModelDescription') }}"
                                    />
                                </flux:field>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <flux:button type="button" variant="primary" wire:click="createNewModel">
                                Create Model
                            </flux:button>
                            <flux:button type="button" variant="outline" wire:click="$set('showNewModelForm', false)">
                                Cancel
                            </flux:button>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Additional Asset Details -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <flux:field label="Installation Date">
                        <flux:input
                            wire:model="installed_on"
                            type="date"
                            error="{{ $errors->first('installed_on') }}"
                        />
                    </flux:field>
                </div>
                <div>
                    <flux:field label="Serial Number">
                        <flux:input
                            wire:model="serial_number"
                            placeholder="Enter serial number"
                            error="{{ $errors->first('serial_number') }}"
                        />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <flux:button variant="outline" href="{{ $site ? route('sites.show', $site->slug) : route('customers.list') }}" wire:navigate>
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Create Asset
                </flux:button>
            </div>
        </form>
    </div>
</div>
