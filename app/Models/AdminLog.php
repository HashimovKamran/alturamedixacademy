<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $table = 'aa_admin_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
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
