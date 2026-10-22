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
        // 1. TAMBAHKAN BARIS INI (Hapus tabel lama agar tidak error "Duplicate table")
        Schema::dropIfExists('restock_requests');

        // 2. Buat tabel baru dengan struktur lengkap dari Intan
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id'); // Buku mana yang mau diisi?
            $table->uuid('user_id'); // Siapa admin yang mengajukan?
            $table->integer('quantity'); // Jumlah yang diminta
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('pimpinan_notes')->nullable(); // Komentar Pak Andi
            $table->boolean('is_completed')->default(false); // Apakah barang sudah sampai?
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Relasi
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_requests');
    }
};