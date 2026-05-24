<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->constrained('tax_classes')->cascadeOnDelete();
            $table->string('name');
            $table->string('country_code', 5);
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->decimal('rate', 8, 4);
            $table->integer('priority')->default(0);
            $table->boolean('is_compound')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index('country_code');
            $table->index('status');
            $table->index(['tax_class_id', 'country_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
