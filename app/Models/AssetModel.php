<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class AssetModel extends Model
{
    /** @use HasFactory<\Database\Factories\AssetModelFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'asset_brand_id',
        'name',
        'description',
        'model_number',
        'btu_rating',
        'efficiency_rating',
        'is_active',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function assetBrand(): BelongsTo
    {
        return $this->belongsTo(AssetBrand::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }
    protected function casts(): array
    {
        return [
            'btu_rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
