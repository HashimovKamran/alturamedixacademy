<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('aa_page_builder_documents')) {
            Schema::create('aa_page_builder_documents', function (Blueprint $table): void {
                $table->id();
                $table->string('lang_code', 10);
                $table->string('page_key', 120);
                $table->unsignedSmallInteger('schema_version')->default(2);
                $table->longText('document_json');
                $table->foreignId('updated_by')->nullable()->constrained('aa_admin_users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['lang_code', 'page_key']);
                $table->index(['lang_code', 'page_key', 'schema_version'], 'aa_pb_documents_lookup');
            });
        }

        Schema::table('aa_page_publications', function (Blueprint $table): void {
            if (! Schema::hasColumn('aa_page_publications', 'document_json')) {
                $table->longText('document_json')->nullable()->after('blocks_json');
            }
            if (! Schema::hasColumn('aa_page_publications', 'document_schema_version')) {
                $table->unsignedSmallInteger('document_schema_version')->nullable()->after('document_json');
            }
        });

        Schema::table('aa_page_revisions', function (Blueprint $table): void {
            if (! Schema::hasColumn('aa_page_revisions', 'document_json')) {
                $table->longText('document_json')->nullable()->after('blocks_json');
            }
            if (! Schema::hasColumn('aa_page_revisions', 'document_schema_version')) {
                $table->unsignedSmallInteger('document_schema_version')->nullable()->after('document_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aa_page_revisions', function (Blueprint $table): void {
            if (Schema::hasColumn('aa_page_revisions', 'document_schema_version')) {
                $table->dropColumn('document_schema_version');
            }
            if (Schema::hasColumn('aa_page_revisions', 'document_json')) {
                $table->dropColumn('document_json');
            }
        });

        Schema::table('aa_page_publications', function (Blueprint $table): void {
            if (Schema::hasColumn('aa_page_publications', 'document_schema_version')) {
                $table->dropColumn('document_schema_version');
            }
            if (Schema::hasColumn('aa_page_publications', 'document_json')) {
                $table->dropColumn('document_json');
            }
        });

        Schema::dropIfExists('aa_page_builder_documents');
    }
};
