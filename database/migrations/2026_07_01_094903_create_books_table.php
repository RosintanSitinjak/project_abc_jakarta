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
        // Schema::create('books', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

        Schema::create('books', function (Blueprint $table) {
        $table->uuid('id')->primary(); // Pakai UUID sesuai standar projectmu
        $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
        $table->string('title');
        $table->string('author')->nullable();
        $table->string('isbn')->nullable();
        $table->integer('price'); // Harga umum
        $table->integer('member_price')->nullable(); // Harga khusus Penginjil (PL)
        $table->integer('stock');
        $table->integer('rop_point')->default(10); // Sesuai diskusi: Minimal 10 untuk notifikasi
        $table->text('description')->nullable();
        $table->string('cover_image')->nullable();
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
