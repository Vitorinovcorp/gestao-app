<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_lines', function (Blueprint $table) {
            // Tornar todas as colunas nullable
            $table->decimal('vat_rate', 5, 2)->nullable()->change();
            $table->decimal('line_subtotal', 12, 2)->nullable()->change();
            $table->decimal('line_vat', 12, 2)->nullable()->change();
            $table->decimal('line_total', 12, 2)->nullable()->change();
            $table->decimal('discount_percent', 5, 2)->nullable()->change();
            $table->decimal('discount_value', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('proposal_lines', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->nullable(false)->change();
            $table->decimal('line_subtotal', 12, 2)->nullable(false)->change();
            $table->decimal('line_vat', 12, 2)->nullable(false)->change();
            $table->decimal('line_total', 12, 2)->nullable(false)->change();
            $table->decimal('discount_percent', 5, 2)->nullable(false)->change();
            $table->decimal('discount_value', 12, 2)->nullable(false)->change();
        });
    }
};