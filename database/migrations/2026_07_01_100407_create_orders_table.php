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
        // Schema::create('orders', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

        Schema::create('orders', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('customer_id')->constrained('customers');
        $table->string('order_number')->unique(); // Contoh: INV-202607-001
        
        // 1. PENJUALAN
        $table->integer('total_amount');
        
        // 2. PEMBAYARAN & PIUTANG
        $table->enum('payment_method', ['cash', 'transfer', 'kredit']);
        $table->enum('payment_status', ['pending', 'unpaid', 'paid'])->default('pending');
        $table->string('payment_proof')->nullable(); // Foto bukti transfer
        $table->timestamp('due_date')->nullable(); // Jatuh tempo 30 hari untuk kredit
        
        // 3. PENGIRIMAN
        $table->enum('shipping_status', ['pending', 'processing', 'shipping', 'delivered'])->default('pending');
        $table->string('tracking_number')->nullable();
        $table->string('courier_name')->nullable();
        
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
