<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aa_pages') && ! Schema::hasColumn('aa_pages', 'meta_title')) {
            Schema::table('aa_pages', function (Blueprint $table): void {
                $table->string('meta_title')->nullable()->after('button_url');
                $table->string('meta_image', 700)->nullable()->after('meta_description');
                $table->string('robots', 40)->default('index,follow')->after('meta_image');
            });
        }
        if (Schema::hasTable('aa_articles') && ! Schema::hasColumn('aa_articles', 'meta_title')) {
            Schema::table('aa_articles', function (Blueprint $table): void {
                $table->string('meta_title')->nullable()->after('author_name');
                $table->string('meta_description', 700)->nullable()->after('meta_title');
                $table->string('meta_image', 700)->nullable()->after('meta_description');
                $table->string('robots', 40)->default('index,follow')->after('meta_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('aa_pages') && Schema::hasColumn('aa_pages', 'meta_title')) {
            Schema::table('aa_pages', fn (Blueprint $table) => $table->dropColumn(['meta_title', 'meta_image', 'robots']));
        }
        if (Schema::hasTable('aa_articles') && Schema::hasColumn('aa_articles', 'meta_title')) {
            Schema::table('aa_articles', fn (Blueprint $table) => $table->dropColumn(['meta_title', 'meta_description', 'meta_image', 'robots']));
        }
    }
};
