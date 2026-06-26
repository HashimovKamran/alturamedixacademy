<?php

namespace App\Services\Site;

use Illuminate\Support\Collection;

class BlockTreeService
{
    public function build(Collection $blocks): Collection
    {
        $byParent = $blocks->groupBy(fn ($block) => (string) ($block->parent_block_uuid ?: '__root'));
        return $this->branch($byParent, '__root', []);
    }

    public function flatten(Collection $blocks): Collection
    {
        $flat = collect();
        $walk = function (Collection $nodes, int $depth = 0) use (&$walk, $flat): void {
            foreach ($nodes as $node) {
                $flat->push(['block' => $node['block'], 'depth' => $depth]);
                $walk($node['children'], $depth + 1);
            }
        };
        $walk($this->build($blocks));
        return $flat;
    }

    private function branch(Collection $byParent, string $parent, array $ancestors): Collection
    {
        if (in_array($parent, $ancestors, true) || count($ancestors) >= 8) return collect();
        $ancestors[] = $parent;

        return collect($byParent->get($parent, collect()))->sortBy([
            ['sort_order', 'asc'], ['id', 'asc'],
        ])->map(fn ($block) => [
            'block' => $block,
            'children' => $this->branch($byParent, (string) $block->block_uuid, $ancestors),
        ])->values();
    }
}
