<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = 'aa_trainings';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'training_date' => 'date',
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
