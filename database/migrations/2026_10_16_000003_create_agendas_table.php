<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('tanggal');
            $table->string('waktu', 5)->nullable();
            $table->string('category')->default('workshop');
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
