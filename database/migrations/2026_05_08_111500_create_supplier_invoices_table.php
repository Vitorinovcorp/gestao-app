<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->foreignId('supplier_id')->constrained('entities');
            $table->foreignId('supplier_order_id')->constrained('supplier_orders');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('vat_total', 12, 2);
            $table->decimal('total_value', 12, 2);
            $table->string('document_path')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->text('observations')->nullable();
            $table->date('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('supplier_id');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};