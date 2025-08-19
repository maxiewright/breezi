<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Task extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'site_id',
        'type',
        'title',
        'description',
        'status',
        'scheduled_at',
        'completed_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['title', 'scheduled_at'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'task_id');
    }

    public function customer(): ?Customer
    {
        return $this->site?->customer;
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class)
            ->withPivot([
                'service_notes',
                'condition_before',
                'condition_after',
                'filter_changed',
                'parts_replaced',
                'parts_list',
                'labor_hours',
            ])
            ->withTimestamps();
    }
    protected function casts(): array
    {
        return [
            'type' => TaskType::class,
            'status' => TaskStatus::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
