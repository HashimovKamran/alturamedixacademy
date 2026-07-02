<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('aa_trainings') || Schema::hasColumn('aa_trainings', 'cover_image')) {
            return;
        }

        Schema::table('aa_trainings', function (Blueprint $table): void {
            $table->string('cover_image')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('aa_trainings') || ! Schema::hasColumn('aa_trainings', 'cover_image')) {
            return;
        }

        Schema::table('aa_trainings', function (Blueprint $table): void {
            $table->dropColumn('cover_image');
        });
    }
};
