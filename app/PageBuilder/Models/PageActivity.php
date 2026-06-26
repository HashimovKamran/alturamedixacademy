<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PageActivity extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    protected $table = 'page_builder_activities';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }
}

