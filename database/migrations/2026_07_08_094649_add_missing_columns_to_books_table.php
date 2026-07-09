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
    Schema::table('books', function (Blueprint $table) {
        if (!Schema::hasColumn('books', 'member_price')) {
            $table->integer('member_price')->nullable()->after('price');
        }
        if (!Schema::hasColumn('books', 'description')) {
            $table->text('description')->nullable();
        }
        if (!Schema::hasColumn('books', 'thumbnail_id')) {
            $table->uuid('thumbnail_id')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            //
        });
    }
};
