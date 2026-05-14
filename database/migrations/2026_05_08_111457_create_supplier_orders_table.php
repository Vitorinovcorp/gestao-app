<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('order_date');
            $table->foreignId('supplier_id')->constrained('entities');
            $table->foreignId('customer_order_id')->nullable()->constrained('orders');
            $table->enum('status', ['draft', 'sent', 'confirmed', 'received', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->string('tracking_code')->nullable();
            $table->date('expected_delivery')->nullable();
            $table->datetime('ordered_at')->nullable();
            $table->datetime('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('order_date');
            $table->index('supplier_id');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_orders');
    }
};