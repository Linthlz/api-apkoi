<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "title" dipakai sebagai nama program/kegiatan sertifikat, sehingga boleh sama
     * untuk banyak penerima. Keunikan dijaga oleh slug. Tambah asal institusi penerima.
     */
    public function up(): void
    {
        Schema::table('sertifikasis', function (Blueprint $table) {
            $table->dropUnique(['title']);
        });

        Schema::table('sertifikasis', function (Blueprint $table) {
            $table->string('institution')->nullable()->after('sertificate_owner');
        });
    }

    public function down(): void
    {
        Schema::table('sertifikasis', function (Blueprint $table) {
            $table->dropColumn('institution');
            $table->unique('title');
        });
    }
};
