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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subCategories(): HasMany
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
}
