<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('supplier_id')->nullable()->constrained('entities');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 4);
            $table->decimal('cost_price', 12, 4)->nullable();
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
            $table->index('proposal_id');
            $table->index('article_id');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_lines');
    }
};