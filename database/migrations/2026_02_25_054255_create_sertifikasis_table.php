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
        Schema::create('sertifikasis', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id');
            $table->string('title');
            $table->string('sertificate_owner')->nullable();
            $table->string('sertificate_code')->nullable();
            $table->string('sertificate_image')->nullable();
            $table->string('sertificate_file')->nullable();
            $table->string('slug');
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->string('tanggal');
            $table->string('published_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikasis');
    }
};
