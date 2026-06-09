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
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
