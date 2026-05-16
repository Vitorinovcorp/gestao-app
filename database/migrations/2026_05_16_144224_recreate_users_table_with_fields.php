<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Não recriar se quiser preservar dados
        Schema::table('users', function (Blueprint $table) {
            // Verificar e adicionar colunas uma por uma
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 20)->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('users', 'grupo_permissoes')) {
                $table->string('grupo_permissoes')->default('visualizador')->after('telefone');
            }
            
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('grupo_permissoes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'grupo_permissoes', 'status']);
        });
    }
};