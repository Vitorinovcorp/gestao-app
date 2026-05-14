<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_order_id')->constrained('supplier_orders')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('order_line_id')->nullable()->constrained('order_lines');
            $table->integer('quantity');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 12, 4);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('line_subtotal', 12, 2);
            $table->decimal('line_vat', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('supplier_order_id');
            $table->index('article_id');
            $table->index('order_line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_order_lines');
    }
};