<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->foreignId('vat_id')->constrained('vat_rates');
            $table->string('photo_path')->nullable();
            $table->string('barcode', 50)->nullable();
            $table->integer('stock_min')->default(0);
            $table->integer('stock_current')->default(0);
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('reference');
            $table->index('name');
            $table->index('is_active');
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};