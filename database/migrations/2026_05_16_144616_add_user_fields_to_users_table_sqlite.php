<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            
            // Adicionar coluna telefone se não existir
            if (!Schema::hasColumn('users', 'telefone')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('telefone', 20)->nullable();
                });
            }
            
            // Adicionar coluna grupo_permissoes se não existir
            if (!Schema::hasColumn('users', 'grupo_permissoes')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('grupo_permissoes')->default('visualizador');
                });
            }
            
            // Adicionar coluna status se não existir
            if (!Schema::hasColumn('users', 'status')) {
                Schema::table('users', function (Blueprint $table) {
                    // SQLite não suporta enum, usar string
                    $table->string('status')->default('active');
                });
            }
            
            // Adicionar colunas 2FA se não existirem
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->text('two_factor_secret')->nullable();
                });
            }
            
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->text('two_factor_recovery_codes')->nullable();
                });
            }
            
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('two_factor_confirmed_at')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['telefone', 'grupo_permissoes', 'status', 
                       'two_factor_secret', 'two_factor_recovery_codes', 
                       'two_factor_confirmed_at'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};