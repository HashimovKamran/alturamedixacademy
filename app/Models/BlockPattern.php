<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlockPattern extends Model
{
    protected $table = 'aa_block_patterns';
    protected $guarded = [];
    protected $casts = ['blocks_json' => 'array', 'is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
