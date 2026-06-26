<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VisualBlock extends Model
{
    protected $table = 'aa_visual_blocks';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('lang_code', $language);
    }
}