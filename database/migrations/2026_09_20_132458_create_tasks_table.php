<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('week');
            $table->string('matkul');
            $table->string('tugas');
            $table->date('deadline')->nullable();
            $table->enum('tipe', ['Individu', 'Kelompok'])->default('Individu');
            $table->enum('status', ['Not started', 'In progress', 'Done'])->default('Not started');
            $table->string('files_media', 500)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};