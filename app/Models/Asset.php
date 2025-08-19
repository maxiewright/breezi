<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Asset extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'site_id',
        'asset_brand_id',
        'asset_model_id',
        'name',
        'installed_on',
        'serial_number',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['name', 'serial_number'])
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function assetBrand(): BelongsTo
    {
        return $this->belongsTo(AssetBrand::class);
    }

    public function assetModel(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class);
    }

    protected function brandName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn() => $this->assetBrand->name);
    }

    protected function modelName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn() => $this->assetModel->name);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)
            ->withPivot([
                'service_notes',
                'condition_before',
                'condition_after',
                'filter_changed',
                'parts_replaced',
                'parts_list',
                'labor_hours',
            ])
            ->withTimestamps()
            ->orderBy('scheduled_at', 'desc');
    }

    // Alias for backward compatibility
    public function serviceJobs(): BelongsToMany
    {
        return $this->tasks();
    }
    protected function lastServiced(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            $lastTask = $this->tasks()
                ->where('status', 'completed')
                ->first();
            return $lastTask?->completed_at?->format('M j, Y') ?? 'Never serviced';
        });
    }
    protected function casts(): array
    {
        return [
            'installed_on' => 'date',
        ];
    }
}
