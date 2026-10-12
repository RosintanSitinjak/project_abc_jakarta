<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('debt_payments', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('customer_id'); // Siapa yang bayar
        $table->integer('amount');   // Berapa jumlahnya (misal 10k)
        $table->string('payment_method');
        $table->uuid('attachment_id')->nullable(); // BUKTI FOTO DISIMPAN DI SINI
        $table->text('note')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
