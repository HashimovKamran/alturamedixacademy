<?php

namespace App\PageBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasUlids;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'page_builder_pages';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class, 'page_id');
    }

    public function activeRevision(): BelongsTo
    {
        return $this->belongsTo(PageRevision::class, 'active_revision_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PageActivity::class, 'page_id');
    }
}

