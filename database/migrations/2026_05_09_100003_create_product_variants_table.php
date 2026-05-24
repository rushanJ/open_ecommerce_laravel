<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 100)->nullable()->index();
            $table->string('name')->nullable();
            $table->decimal('regular_price', 15, 4)->default(0);
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->decimal('stock_quantity', 15, 4)->nullable();
            $table->string('stock_status', 30)->default('in_stock')->index();
            $table->decimal('weight', 15, 4)->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
