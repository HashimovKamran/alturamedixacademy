<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'aa_articles';

    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('lang_code', $language);
    }
    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}