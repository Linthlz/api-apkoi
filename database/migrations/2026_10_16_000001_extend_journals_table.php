<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah data yang dibutuhkan halaman Jurnal di frontend:
     * nama jurnal, penerbit, link artikel, dan anggota pemilik artikel.
     */
    public function up(): void
    {
        // Jaga-jaga bila migration sebelumnya gagal menghapus kolom ini
        if (Schema::hasColumn('journals', 'journal_file')) {
            Schema::table('journals', function (Blueprint $table) {
                $table->dropColumn('journal_file');
            });
        }

        Schema::table('journals', function (Blueprint $table) {
            $table->string('journal_name')->nullable()->after('journal_author');
            $table->string('journal_publisher')->nullable()->after('journal_name');
            $table->string('url', 1000)->nullable()->after('doi');
            $table->string('member_name')->nullable()->after('url');
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->text('journal_abstract')->nullable()->change();
            $table->text('journal_author')->change();
            $table->string('doi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['journal_name', 'journal_publisher', 'url', 'member_name']);
        });
    }
};
