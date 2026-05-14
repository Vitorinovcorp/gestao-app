<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->date('proposal_date');
            $table->foreignId('client_id')->constrained('entities');
            $table->date('validity');
            $table->enum('status', ['draft', 'sent', 'closed', 'rejected'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_total', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->datetime('sent_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('proposal_date');
            $table->index('client_id');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};