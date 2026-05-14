<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir tipos padrão
        DB::table('calendar_types')->insert([
            ['name' => 'Reunião', 'color' => '#3B82F6', 'is_active' => true],
            ['name' => 'Chamada', 'color' => '#10B981', 'is_active' => true],
            ['name' => 'Visita', 'color' => '#F59E0B', 'is_active' => true],
            ['name' => 'Prazo', 'color' => '#EF4444', 'is_active' => true],
            ['name' => 'Tarefa', 'color' => '#8B5CF6', 'is_active' => true],
            ['name' => 'Evento', 'color' => '#EC4899', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_types');
    }
};