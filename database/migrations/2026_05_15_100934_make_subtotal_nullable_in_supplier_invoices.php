<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->nullable()->change();
            $table->decimal('vat_total', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->nullable(false)->change();
            $table->decimal('vat_total', 12, 2)->nullable(false)->change();
        });
    }
};