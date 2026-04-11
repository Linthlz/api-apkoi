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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id');
            $table->string('title');
            $table->string('journal_author');
            $table->date('publish_date');
            $table->string('doi');
            $table->string('journal_abstract');
            $table->string('journal_file');
            $table->string('slug');
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal');
    }
};
