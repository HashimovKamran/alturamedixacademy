<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aa_admin_users') && ! Schema::hasColumn('aa_admin_users', 'role')) {
            Schema::table('aa_admin_users', fn (Blueprint $table) => $table->string('role', 40)->default('super_admin')->after('full_name')->index());
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('aa_admin_users') && Schema::hasColumn('aa_admin_users', 'role')) {
            Schema::table('aa_admin_users', fn (Blueprint $table) => $table->dropColumn('role'));
        }
    }
};
