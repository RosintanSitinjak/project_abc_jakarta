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
    Schema::create('customers', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
        $table->string('name');
        
        // 1. UPDATE TIPE: jemaat, gereja, sekolah, penginjil
        $table->enum('type', ['jemaat', 'gereja', 'sekolah', 'penginjil'])->default('jemaat');
        
        // 2. TAMBAH STATUS: Untuk verifikasi PL oleh Admin
        // Default 'approved' supaya jemaat biasa/gereja bisa langsung pakai
        // Nanti di kodingan pendaftaran, kalau pilih 'penginjil' kita set ke 'pending'
        $table->string('status')->default('approved'); 

        $table->text('address')->nullable();
        $table->string('phone')->nullable();
        
        // 3. ATURAN KREDIT
        $table->integer('credit_limit')->default(0); 
        $table->integer('current_debt')->default(0); 
        
        $table->timestamps();
        $table->softDeletes();
    });
}    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
