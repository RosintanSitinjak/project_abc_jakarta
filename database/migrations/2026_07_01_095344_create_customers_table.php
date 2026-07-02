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
        // Schema::create('customers', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->timestamps();
        //     $table->softDeletes();

        Schema::create('customers', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
        $table->string('name');
        $table->enum('type', ['gereja', 'penginjil', 'umum'])->default('umum');
        $table->text('address')->nullable();
        $table->string('phone')->nullable();
        $table->integer('credit_limit')->default(0); // Sesuai diskusi: Penginjil PL maks 5jt
        $table->integer('current_debt')->default(0); // Untuk memantau total hutang berjalan
        $table->timestamps();
        $table->softDeletes();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
