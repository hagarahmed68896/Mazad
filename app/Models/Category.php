<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'image', 'ad_types', 'deposit_value', 'tax_value'];

    protected $casts = [
        'ad_types' => 'array',
    ];

    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('parent_categories_list'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('parent_categories_list'));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(CategoryPage::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function getImageAttribute($value)
    {
        if (!$value) {
            return 'https://mazad-admin.com/uploads/categories/placeholder.png';
        }

        if (\Illuminate\Support\Str::startsWith($value, 'http')) {
            return $value;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'uploads/')) {
            return asset($value);
        }

        if (\Illuminate\Support\Str::startsWith($value, 'categories/')) {
            return asset('storage/' . $value);
        }

        return asset('uploads/' . $value);
    }
}
