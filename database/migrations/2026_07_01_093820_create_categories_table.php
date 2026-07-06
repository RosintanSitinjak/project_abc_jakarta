<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
        // Schema::create('categories', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

    //     Schema::create('categories', function (Blueprint $table) {
    //     $table->uuid('id')->primary(); // Tetap pakai UUID bawaan projectmu
    //     $table->string('name');        // TAMBAHKAN INI (Untuk nama: Rohani, Kesehatan, dll)
    //     $table->string('slug')->unique(); // TAMBAHKAN INI (Untuk URL rapi)
    //     $table->timestamps();
    //     $table->softDeletes();         // Tetap biarkan, ini fitur bagus
    //     });
    // }


    public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable(); // PASTIKAN BARIS INI ADA
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
