<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('order_date');
            $table->foreignId('client_id')->constrained('entities');
            $table->enum('status', ['rascunho', 'confirmada', 'processamento', 'enviada', 'entregue', 'cancelada'])->default('rascunho');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('proposal_id')->nullable()->constrained('proposals');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->datetime('confirmed_at')->nullable();
            $table->date('expected_delivery')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('order_date');
            $table->index('client_id');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
