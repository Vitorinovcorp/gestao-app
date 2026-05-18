<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Verifica se a coluna não existe antes de adicionar
            if (!Schema::hasColumn('company_settings', 'logo_path')) {
                $table->string('logo_path')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'name')) {
                $table->string('name')->default('Minha Empresa');
            }
            if (!Schema::hasColumn('company_settings', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'tax_number')) {
                $table->string('tax_number')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'website')) {
                $table->string('website')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path', 'name', 'address', 'postal_code', 
                'city', 'tax_number', 'email', 'phone', 'website'
            ]);
        });
    }
};