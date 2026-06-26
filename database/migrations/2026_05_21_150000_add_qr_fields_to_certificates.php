<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aa_certificates', function (Blueprint $table): void {
            $table->string('qr_code_path', 700)->nullable()->after('file_path');
            $table->string('qr_document_path', 700)->nullable()->after('qr_code_path');
            $table->unsignedTinyInteger('qr_x')->default(72)->after('qr_document_path');
            $table->unsignedTinyInteger('qr_y')->default(72)->after('qr_x');
            $table->unsignedTinyInteger('qr_size')->default(16)->after('qr_y');
        });
    }

    public function down(): void
    {
        Schema::table('aa_certificates', function (Blueprint $table): void {
            $table->dropColumn(['qr_code_path', 'qr_document_path', 'qr_x', 'qr_y', 'qr_size']);
        });
    }
};
