<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageBuilderBlock extends Model
{
    protected $table = 'aa_page_builder_blocks';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'content_json' => 'array',
        'schema_version' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (PageBuilderBlock $block): void {
            $block->block_uuid ??= (string) Str::uuid();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('lang_code', $language);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_block_uuid', 'block_uuid')
            ->orderBy('sort_order')->orderBy('id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_block_uuid', 'block_uuid');
    }
}
