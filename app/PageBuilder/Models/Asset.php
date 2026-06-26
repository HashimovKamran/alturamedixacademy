<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $table = 'page_builder_assets';

    protected $guarded = [];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}

