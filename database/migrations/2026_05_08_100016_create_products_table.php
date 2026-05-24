<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('product_type', 30)->default('simple');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 100)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('visibility', 30)->default('visible');
            $table->boolean('is_featured')->default(false);
            $table->decimal('regular_price', 15, 4)->default(0);
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->nullOnDelete();
            $table->boolean('manage_stock')->default(false);
            $table->decimal('stock_quantity', 15, 4)->nullable();
            $table->decimal('low_stock_threshold', 15, 4)->nullable();
            $table->string('stock_status', 30)->default('in_stock');
            $table->boolean('backorders_allowed')->default(false);
            $table->decimal('weight', 15, 4)->nullable();
            $table->decimal('length', 15, 4)->nullable();
            $table->decimal('width', 15, 4)->nullable();
            $table->decimal('height', 15, 4)->nullable();
            $table->string('digital_file_path')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('barcode');
            $table->index('visibility');
            $table->index('stock_status');
            $table->index(['status', 'visibility']);
            $table->index(['product_type', 'status']);
            $table->index(['is_featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
