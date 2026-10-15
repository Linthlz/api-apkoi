<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nama tabel yang benar adalah "journals"; dijaga agar aman bila kolom sudah terhapus
        if (Schema::hasTable('journals') && Schema::hasColumn('journals', 'journal_file')) {
            Schema::table('journals', function(Blueprint $table){
                $table->dropColumn('journal_file');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
