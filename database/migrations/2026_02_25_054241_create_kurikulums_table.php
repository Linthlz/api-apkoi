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
        Schema::create('kurikulums', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id');
            $table->string('title');
            $table->string('tanggal');
            $table->string('kurikulum_image')->nullable();
            $table->string('kurikulum_file')->nullable();
            // $table->string('content');
            $table->string('slug');
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->string('published_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulums');
    }
};
