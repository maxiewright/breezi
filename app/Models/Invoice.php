<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Invoice extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'task_id',
        'number',
        'status',
        'total',
        'notes',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    // Alias for backward compatibility
    public function job(): BelongsTo
    {
        return $this->task();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
    protected function customer(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn() => $this->task?->site?->customer);
    }
    protected function site(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn() => $this->task?->site);
    }
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }
}
