<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class AssetBrand extends Model
{
    /** @use HasFactory<\Database\Factories\AssetBrandFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'description',
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

    public function assetModels(): HasMany
    {
        return $this->hasMany(AssetModel::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
