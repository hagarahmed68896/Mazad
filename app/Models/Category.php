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

    protected $appends = ['is_real_estate', 'is_cars'];

    protected $casts = [
        'ad_types' => 'array',
    ];

    public function getIsRealEstateAttribute(): bool
    {
        return $this->isRealEstate();
    }

    public function getIsCarsAttribute(): bool
    {
        return $this->isCars();
    }

    public function isRealEstate(): bool
    {
        $cat = $this;
        while ($cat) {
            $name = mb_strtolower($cat->name);
            if (
                str_contains($name, 'عقار') || 
                str_contains($name, 'العقارات') || 
                str_contains($name, 'عقارات') || 
                str_contains($name, 'real estate') || 
                str_contains($name, 'realestate') ||
                str_contains($name, 'property') ||
                str_contains($name, 'properties')
            ) {
                return true;
            }
            $cat = $cat->parent_id ? ($cat->parent ?: $cat->load('parent')->parent) : null;
        }
        return false;
    }

    public function isCars(): bool
    {
        $cat = $this;
        while ($cat) {
            $name = mb_strtolower($cat->name);
            if (
                str_contains($name, 'سيارات') || 
                str_contains($name, 'سيارة') || 
                str_contains($name, 'car') || 
                str_contains($name, 'cars') || 
                str_contains($name, 'vehicle') || 
                str_contains($name, 'vehicles')
            ) {
                return true;
            }
            $cat = $cat->parent_id ? ($cat->parent ?: $cat->load('parent')->parent) : null;
        }
        return false;
    }

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
        $name = mb_strtolower($this->name);
        $host = app()->runningInConsole() ? asset('') : request()->getSchemeAndHttpHost() . request()->getBaseUrl();
        $host = rtrim($host, '/');
        
        // 1. Check if there is a custom user-uploaded image in the DB (starts with uploads/ or categories/ and is not a placeholder)
        $hasCustomImage = false;
        if ($value) {
            $isUpload = \Illuminate\Support\Str::startsWith($value, 'uploads/') || 
                        \Illuminate\Support\Str::startsWith($value, 'categories/');
            
            $isPlaceholder = str_contains($value, 'placeholder.png') || 
                             str_contains($value, 'car_placeholder.svg');
            
            if ($isUpload && !$isPlaceholder) {
                $hasCustomImage = true;
            }
        }

        // 2. If a custom user-uploaded image is present, serve it
        if ($hasCustomImage) {
            if (\Illuminate\Support\Str::startsWith($value, 'http')) {
                return $value;
            }

            if (\Illuminate\Support\Str::startsWith($value, 'uploads/')) {
                return $host . '/' . $value;
            }

            if (\Illuminate\Support\Str::startsWith($value, 'categories/')) {
                return $host . '/storage/' . $value;
            }

            return $host . '/uploads/' . $value;
        }

        // 3. Fallback to our custom line art icons if no custom image is uploaded
        if (
            str_contains($name, 'سيارات') || 
            str_contains($name, 'سيارة') || 
            str_contains($name, 'car') || 
            str_contains($name, 'cars') || 
            str_contains($name, 'vehicle') || 
            str_contains($name, 'vehicles')
        ) {
            return $host . '/images/cars_category.png?v=3';
        }

        if (
            str_contains($name, 'عقار') || 
            str_contains($name, 'العقارات') || 
            str_contains($name, 'عقارات') || 
            str_contains($name, 'real estate') || 
            str_contains($name, 'realestate')
        ) {
            return $host . '/images/real_estate_category.png?v=3';
        }

        if (
            str_contains($name, 'الكترونيات') || 
            str_contains($name, 'إلكترونيات') || 
            str_contains($name, 'electronics')
        ) {
            return $host . '/images/electronics_category.png?v=3';
        }

        if (
            str_contains($name, 'الأجهزة') || 
            str_contains($name, 'الاقسام') || 
            str_contains($name, 'أجهزة')
        ) {
            return $host . '/images/appliances_category.png?v=3';
        }

        if (
            str_contains($name, 'أثاث') || 
            str_contains($name, 'اثاث') || 
            str_contains($name, 'furniture')
        ) {
            return $host . '/images/furniture_category.png?v=3';
        }

        // 4. Default placeholder fallback if everything else fails
        if (!$value) {
            return 'https://mazad-admin.com/uploads/categories/placeholder.png';
        }

        if (\Illuminate\Support\Str::startsWith($value, 'http')) {
            return $value;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'images/')) {
            return $host . '/' . $value;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'uploads/')) {
            return $host . '/' . $value;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'categories/')) {
            return $host . '/storage/' . $value;
        }

        return $host . '/uploads/' . $value;
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
