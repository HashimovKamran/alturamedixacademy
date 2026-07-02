<?php

namespace App\AlturaPageBuilder\Services;

use Illuminate\Support\Facades\DB;

final class VisualPageMetaWriter
{
    public function write(int $pageId, array $meta): void
    {
        $updates = [
            'meta_title' => $this->plain($meta['meta_title'] ?? null, 255),
            'meta_description' => $this->plain($meta['meta_description'] ?? null, 500),
            'meta_keywords' => $this->plain($meta['meta_keywords'] ?? null, 255),
            'template' => $this->plain($meta['template'] ?? null, 120),
            'is_archived' => false,
            'is_deleted' => false,
            'lock_version' => DB::raw('lock_version + 1'),
            'updated_at' => now(),
        ];

        $title = $this->plain($meta['title'] ?? null, 255);
        if ($title !== null) {
            $updates['title'] = $title;
        }

        DB::table('aa_visual_pages')->where('id', $pageId)->update($updates);
    }

    private function plain(mixed $value, int $max): ?string
    {
        $value = trim(strip_tags(is_scalar($value) ? (string) $value : ''));
        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
