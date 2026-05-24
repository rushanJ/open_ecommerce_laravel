<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->cascadeOnDelete();
            $table->decimal('min_order_amount', 15, 4)->nullable();
            $table->decimal('max_order_amount', 15, 4)->nullable();
            $table->decimal('min_weight', 15, 4)->nullable();
            $table->decimal('max_weight', 15, 4)->nullable();
            $table->decimal('rate', 15, 4)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['shipping_zone_id', 'shipping_method_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
