<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir funções padrão
        DB::table('contact_roles')->insert([
            ['name' => 'Diretor', 'slug' => 'director', 'description' => 'Diretor da empresa', 'is_active' => true],
            ['name' => 'Gerente', 'slug' => 'manager', 'description' => 'Gerente geral', 'is_active' => true],
            ['name' => 'Financeiro', 'slug' => 'financial', 'description' => 'Departamento financeiro', 'is_active' => true],
            ['name' => 'Comercial', 'slug' => 'commercial', 'description' => 'Departamento comercial', 'is_active' => true],
            ['name' => 'Apoio ao Cliente', 'slug' => 'customer-support', 'description' => 'Suporte ao cliente', 'is_active' => true],
            ['name' => 'Técnico', 'slug' => 'technical', 'description' => 'Departamento técnico', 'is_active' => true],
            ['name' => 'Comprador', 'slug' => 'buyer', 'description' => 'Departamento de compras', 'is_active' => true],
            ['name' => 'Administrativo', 'slug' => 'administrative', 'description' => 'Departamento administrativo', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_roles');
    }
};