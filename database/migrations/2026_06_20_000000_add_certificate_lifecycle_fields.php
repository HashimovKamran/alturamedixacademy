<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aa_certificates', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->index()->after('status');
            $table->text('revoke_reason')->nullable()->after('is_active');
            $table->decimal('qr_x', 6, 3)->default(72)->change();
            $table->decimal('qr_y', 6, 3)->default(72)->change();
            $table->decimal('qr_size', 6, 3)->default(16)->change();
        });
    }

    public function down(): void
    {
        Schema::table('aa_certificates', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['is_active', 'revoke_reason']);
            $table->unsignedTinyInteger('qr_x')->default(72)->change();
            $table->unsignedTinyInteger('qr_y')->default(72)->change();
            $table->unsignedTinyInteger('qr_size')->default(16)->change();
        });
    }
};
