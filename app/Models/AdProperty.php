<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdProperty extends Model
{
    use HasFactory;

    protected $fillable = ['ad_id', 'property_id', 'value', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean'
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getValueAttribute($value)
    {
        if ($value && \Illuminate\Support\Str::startsWith($value, 'uploads/')) {
            $host = app()->runningInConsole() ? asset('') : request()->getSchemeAndHttpHost() . request()->getBaseUrl();
            $host = rtrim($host, '/');
            return $host . '/' . ltrim($value, '/');
        }
        return $value;
    }
}
