<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MirrorAzContentSeeder extends Seeder
{
    private const SOURCE_LANGUAGE = 'az';

    private const TARGET_LANGUAGES = ['en', 'ru', 'tr'];

    public function run(): void
    {
        $now = now();

        foreach (self::TARGET_LANGUAGES as $targetLanguage) {
            DB::transaction(function () use ($targetLanguage, $now): void {
                $this->cloneMenus($targetLanguage, $now);

                foreach ([
                    'aa_settings',
                    'aa_pages',
                    'aa_sliders',
                    'aa_home_stats',
                    'aa_trainings',
                    'aa_features',
                    'aa_blocks',
                    'aa_partners',
                    'aa_ads',
                    'aa_gallery',
                    'aa_page_builder_blocks',
                    'aa_visual_edits',
                    'aa_visual_blocks',
                ] as $table) {
                    $this->cloneSimpleTable($table, $targetLanguage, $now);
                }

                DB::table('aa_articles')->where('lang_code', $targetLanguage)->delete();
                DB::table('aa_article_categories')->where('lang_code', $targetLanguage)->delete();

                $categoryIds = $this->cloneArticleCategories($targetLanguage, $now);
                $this->cloneArticles($targetLanguage, $categoryIds, $now);
            });
        }
    }

    private function cloneSimpleTable(string $table, string $targetLanguage, mixed $now): void
    {
        DB::table($table)->where('lang_code', $targetLanguage)->delete();

        foreach (DB::table($table)->where('lang_code', self::SOURCE_LANGUAGE)->orderBy('id')->get() as $row) {
            DB::table($table)->insert($this->rowData($row, $targetLanguage, $now));
        }
    }

    private function cloneMenus(string $targetLanguage, mixed $now): void
    {
        DB::table('aa_menus')->where('lang_code', $targetLanguage)->delete();

        $pending = DB::table('aa_menus')
            ->where('lang_code', self::SOURCE_LANGUAGE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('id')
            ->all();

        $idMap = [];

        while ($pending !== []) {
            $progress = false;

            foreach ($pending as $oldId => $row) {
                $parentId = (int) ($row->parent_id ?? 0);
                $parentIsPending = $parentId > 0 && isset($pending[$parentId]);

                if ($parentIsPending && ! isset($idMap[$parentId])) {
                    continue;
                }

                $data = $this->rowData($row, $targetLanguage, $now);
                $data['parent_id'] = $parentId > 0 ? ($idMap[$parentId] ?? null) : null;

                $idMap[(int) $oldId] = DB::table('aa_menus')->insertGetId($data);
                unset($pending[$oldId]);
                $progress = true;
            }

            if (! $progress) {
                foreach ($pending as $oldId => $row) {
                    $data = $this->rowData($row, $targetLanguage, $now);
                    $data['parent_id'] = null;
                    $idMap[(int) $oldId] = DB::table('aa_menus')->insertGetId($data);
                    unset($pending[$oldId]);
                }
            }
        }
    }

    private function cloneArticleCategories(string $targetLanguage, mixed $now): array
    {
        $categoryIds = [];

        foreach (DB::table('aa_article_categories')->where('lang_code', self::SOURCE_LANGUAGE)->orderBy('id')->get() as $row) {
            $categoryIds[(int) $row->id] = DB::table('aa_article_categories')->insertGetId(
                $this->rowData($row, $targetLanguage, $now)
            );
        }

        return $categoryIds;
    }

    private function cloneArticles(string $targetLanguage, array $categoryIds, mixed $now): void
    {
        foreach (DB::table('aa_articles')->where('lang_code', self::SOURCE_LANGUAGE)->orderBy('id')->get() as $row) {
            $data = $this->rowData($row, $targetLanguage, $now);
            $data['category_id'] = $row->category_id ? ($categoryIds[(int) $row->category_id] ?? null) : null;

            DB::table('aa_articles')->insert($data);
        }
    }

    private function rowData(object $row, string $targetLanguage, mixed $now): array
    {
        $data = (array) $row;
        unset($data['id']);

        $data['lang_code'] = $targetLanguage;

        if (array_key_exists('created_at', $data)) {
            $data['created_at'] = $now;
        }

        if (array_key_exists('updated_at', $data)) {
            $data['updated_at'] = $now;
        }

        return $data;
    }
}
