<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type'
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getFilePathAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'http')) {
            return $value;
        }

        $host = app()->runningInConsole() ? asset('') : request()->getSchemeAndHttpHost() . request()->getBaseUrl();
        $host = rtrim($host, '/');

        return $host . '/' . ltrim($value, '/');
    }
}
