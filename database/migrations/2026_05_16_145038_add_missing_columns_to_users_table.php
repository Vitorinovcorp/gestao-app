<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adicionar coluna telefone
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 20)->nullable();
            }
            
            // Adicionar coluna grupo_permissoes
            if (!Schema::hasColumn('users', 'grupo_permissoes')) {
                $table->string('grupo_permissoes')->default('visualizador');
            }
            
            // Adicionar coluna status (SQLite não suporta enum, usar string)
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['telefone', 'grupo_permissoes', 'status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};