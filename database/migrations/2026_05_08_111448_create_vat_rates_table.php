<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir taxas de IVA padrão (Portugal)
        DB::table('vat_rates')->insert([
            ['code' => 'NOR', 'name' => 'Normal', 'rate' => 23.00, 'description' => 'Taxa normal de IVA', 'is_active' => true],
            ['code' => 'INT', 'name' => 'Intermediário', 'rate' => 13.00, 'description' => 'Taxa intermédia de IVA', 'is_active' => true],
            ['code' => 'RED', 'name' => 'Reduzido', 'rate' => 6.00, 'description' => 'Taxa reduzida de IVA', 'is_active' => true],
            ['code' => 'ISE', 'name' => 'Isento', 'rate' => 0.00, 'description' => 'Isento de IVA', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};