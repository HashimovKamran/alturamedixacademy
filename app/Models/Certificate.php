<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'aa_certificates';

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'expire_date' => 'date',
        'is_active' => 'boolean',
        'qr_x' => 'float',
        'qr_y' => 'float',
        'qr_size' => 'float',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('lang_code', $language);
    }

    public function isExpired(): bool
    {
        return $this->expire_date !== null && $this->expire_date->lt(today());
    }

    public function effectiveStatus(): string
    {
        if ($this->status === 'revoked') {
            return 'revoked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return $this->status ?: 'unknown';
    }
}
