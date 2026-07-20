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
    Schema::table('customers', function (Blueprint $table) {
        // Menambahkan alasan penolakan dan memperluas status
        $table->text('rejection_reason')->nullable()->after('status');
        // Status: pending, approved, rejected, suspended
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
