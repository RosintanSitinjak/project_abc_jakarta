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
        // Schema::create('restock_requests', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

        Schema::create('restock_requests', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('book_id')->constrained('books');
        $table->integer('qty_requested');
        $table->integer('qty_approved')->nullable(); // Bisa diubah oleh pimpinan
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('admin_note')->nullable(); // Catatan admin ke pimpinan
        $table->text('leader_comment')->nullable(); // Alasan pimpinan approve/tolak
        $table->foreignUuid('created_by')->constrained('users'); // ID Admin yang minta
        $table->foreignUuid('approved_by')->nullable()->constrained('users'); // ID Pimpinan yang approve
        $table->timestamps();
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
