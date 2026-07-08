<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'category_page_id', 'label', 'placeholder', 
        'help_text', 'input_type', 'options', 'is_required', 'is_hideable'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_hideable' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryPage(): BelongsTo
    {
        return $this->belongsTo(CategoryPage::class);
    }
}
