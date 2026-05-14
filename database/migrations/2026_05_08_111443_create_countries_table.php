<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name');
            $table->string('phone_code', 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir países padrão
        DB::table('countries')->insert([
            ['code' => 'PT', 'name' => 'Portugal', 'phone_code' => '351', 'is_active' => true],
            ['code' => 'ES', 'name' => 'Espanha', 'phone_code' => '34', 'is_active' => true],
            ['code' => 'FR', 'name' => 'França', 'phone_code' => '33', 'is_active' => true],
            ['code' => 'DE', 'name' => 'Alemanha', 'phone_code' => '49', 'is_active' => true],
            ['code' => 'IT', 'name' => 'Itália', 'phone_code' => '39', 'is_active' => true],
            ['code' => 'GB', 'name' => 'Reino Unido', 'phone_code' => '44', 'is_active' => true],
            ['code' => 'US', 'name' => 'Estados Unidos', 'phone_code' => '1', 'is_active' => true],
            ['code' => 'BR', 'name' => 'Brasil', 'phone_code' => '55', 'is_active' => true],
            ['code' => 'AO', 'name' => 'Angola', 'phone_code' => '244', 'is_active' => true],
            ['code' => 'MZ', 'name' => 'Moçambique', 'phone_code' => '258', 'is_active' => true],
            ['code' => 'CV', 'name' => 'Cabo Verde', 'phone_code' => '238', 'is_active' => true],
            ['code' => 'ST', 'name' => 'São Tomé e Príncipe', 'phone_code' => '239', 'is_active' => true],
            ['code' => 'GW', 'name' => 'Guiné-Bissau', 'phone_code' => '245', 'is_active' => true],
            ['code' => 'TL', 'name' => 'Timor-Leste', 'phone_code' => '670', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};