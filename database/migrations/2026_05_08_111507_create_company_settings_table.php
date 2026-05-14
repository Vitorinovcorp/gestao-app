<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('tax_number', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('legal_representative')->nullable();
            $table->string('commercial_registry')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->json('payment_methods')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('language', 5)->default('pt');
            $table->string('timezone')->default('Europe/Lisbon');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->timestamps();
        });

        // Inserir configuração padrão da empresa
        DB::table('company_settings')->insert([
            'name' => 'Minha Empresa',
            'email' => 'geral@minhaempresa.com',
            'currency' => 'EUR',
            'language' => 'pt',
            'timezone' => 'Europe/Lisbon',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};