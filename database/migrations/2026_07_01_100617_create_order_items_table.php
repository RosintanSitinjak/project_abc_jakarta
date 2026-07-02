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
        // Schema::create('order_items', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

        Schema::create('order_items', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
        $table->foreignUuid('book_id')->constrained('books');
        $table->integer('qty');
        $table->integer('price_at_purchase'); // Harga saat itu (biar gak berubah kalau harga buku naik)
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
