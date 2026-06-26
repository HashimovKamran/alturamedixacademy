<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_builder_revisions', function (Blueprint $table): void {
            $table->foreign('page_id')
                ->references('id')
                ->on('page_builder_pages')
                ->cascadeOnDelete();
        });

        Schema::table('page_builder_activities', function (Blueprint $table): void {
            $table->foreign('page_id')
                ->references('id')
                ->on('page_builder_pages')
                ->cascadeOnDelete();

            $table->foreign('revision_id')
                ->references('id')
                ->on('page_builder_revisions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_builder_activities', function (Blueprint $table): void {
            $table->dropForeign(['page_id']);
            $table->dropForeign(['revision_id']);
        });

        Schema::table('page_builder_revisions', function (Blueprint $table): void {
            $table->dropForeign(['page_id']);
        });
    }
};
