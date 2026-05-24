<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('product_attribute_values')->nullOnDelete();
            $table->string('custom_value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id', 'attribute_value_id'], 'prod_attr_asg_prod_attr_val_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_assignments');
    }
};
