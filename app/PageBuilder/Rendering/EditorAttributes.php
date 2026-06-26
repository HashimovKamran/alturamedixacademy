<?php

namespace App\PageBuilder\Rendering;

use App\PageBuilder\Components\Component;

class EditorAttributes
{
    public static function active(): bool
    {
        return request()->boolean('pb_editor') && (int) request()->session()->get('admin_user_id', 0) > 0;
    }

    public static function forSection(Component $section): string
    {
        if (! self::active()) {
            return '';
        }

        $meta = self::meta($section);

        return 'data-editor-section=\''.e($meta).'\' data-section-id="'.e($section->id).'" data-block-uuid="'.e($section->block_uuid).'"';
    }

    public static function forBlock(Component $block): string
    {
        if (! self::active()) {
            return '';
        }

        $meta = self::meta($block);

        return 'data-editor-block=\''.e($meta).'\' data-block-id="'.e($block->id).'" data-block-uuid="'.e($block->block_uuid).'"';
    }

    private static function meta(Component $component): string
    {
        return json_encode(array_filter([
            'id' => $component->id,
            'uuid' => $component->block_uuid,
            'type' => $component->type,
            'name' => $component->name,
            'disabled' => $component->disabled ?: null,
        ], fn ($value) => $value !== null), JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}';
    }
}
