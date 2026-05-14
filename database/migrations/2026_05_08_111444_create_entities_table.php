<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['client', 'supplier', 'both'])->default('client');
            $table->string('number', 20)->unique();
            $table->string('nif', 20)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('gdpr_consent')->default(false);
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Dados bancários encriptados
            $table->text('encrypted_bank_data')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes para performance
            $table->index('type');
            $table->index('is_active');
            $table->index('name');
            $table->index('nif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};