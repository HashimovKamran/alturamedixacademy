<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageRevision extends Model
{
    use HasUlids;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'page_builder_revisions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'document' => 'array',
            'theme_settings' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}

