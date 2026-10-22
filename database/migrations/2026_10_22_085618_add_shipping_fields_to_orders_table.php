<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->integer('shipping_fee')->default(0);
            }
            if (!Schema::hasColumn('orders', 'delivery_method')) {
                $table->string('delivery_method', 50)->default('pickup');
            }
            if (!Schema::hasColumn('orders', 'shipping_payment_type')) {
                $table->string('shipping_payment_type', 50)->default('none');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }
        });

        // Update check constraint PostgreSQL agar mendukung QRIS & waiting_shipping_fee
        DB::statement("ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_payment_method_check;");
        DB::statement("ALTER TABLE public.orders ADD CONSTRAINT orders_payment_method_check CHECK (((payment_method)::text = ANY ((ARRAY['cash'::character varying, 'transfer'::character varying, 'qris'::character varying, 'kredit'::character varying])::text[])));");

        DB::statement("ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_payment_status_check;");
        DB::statement("ALTER TABLE public.orders ADD CONSTRAINT orders_payment_status_check CHECK (((payment_status)::text = ANY ((ARRAY['pending'::character varying, 'waiting_shipping_fee'::character varying, 'unpaid'::character varying, 'paid'::character varying, 'cancelled'::character varying])::text[])));");

        DB::statement("ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_shipping_status_check;");
        DB::statement("ALTER TABLE public.orders ADD CONSTRAINT orders_shipping_status_check CHECK (((shipping_status)::text = ANY ((ARRAY['pending'::character varying, 'processing'::character varying, 'shipping'::character varying, 'delivered'::character varying, 'cancelled'::character varying])::text[])));");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee', 'delivery_method', 'shipping_payment_type', 'shipping_address']);
        });
    }
};