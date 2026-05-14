<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('requires_followup')->default(false);
            $table->integer('default_duration')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir ações padrão
        DB::table('calendar_actions')->insert([
            ['name' => 'Follow-up', 'description' => 'Acompanhamento pós-venda', 'requires_followup' => false, 'default_duration' => 30, 'is_active' => true],
            ['name' => 'Apresentação', 'description' => 'Apresentação de produtos/serviços', 'requires_followup' => true, 'default_duration' => 60, 'is_active' => true],
            ['name' => 'Negociação', 'description' => 'Negociação de condições', 'requires_followup' => true, 'default_duration' => 90, 'is_active' => true],
            ['name' => 'Suporte', 'description' => 'Apoio técnico ao cliente', 'requires_followup' => false, 'default_duration' => 45, 'is_active' => true],
            ['name' => 'Formação', 'description' => 'Sessão de formação', 'requires_followup' => false, 'default_duration' => 120, 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_actions');
    }
};