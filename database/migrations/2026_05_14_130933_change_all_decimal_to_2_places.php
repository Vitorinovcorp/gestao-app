<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Artigos
        Schema::table('articles', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
            $table->decimal('cost_price', 12, 2)->nullable()->change();
        });
        
        // Order lines
        Schema::table('order_lines', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->change();
            $table->decimal('cost_price', 12, 2)->nullable()->change();
            $table->decimal('line_subtotal', 12, 2)->change();
            $table->decimal('line_vat', 12, 2)->change();
            $table->decimal('line_total', 12, 2)->change();
        });
        
        // Proposal lines
        Schema::table('proposal_lines', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->change();
            $table->decimal('cost_price', 12, 2)->nullable()->change();
            $table->decimal('line_subtotal', 12, 2)->change();
            $table->decimal('line_vat', 12, 2)->change();
            $table->decimal('line_total', 12, 2)->change();
        });
        
        // Orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_value', 12, 2)->change();
        });
        
        // Proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->decimal('total_value', 12, 2)->change();
        });
    }

    public function down(): void
    {
        // Reverter alterações se necessário
    }
};