<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
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

        // Dados padrão
        DB::table('calendar_actions')->insert([
            ['name' => 'Acompanhamento', 'description' => 'Acompanhamento', 'default_duration' => 30],
            ['name' => 'Apresentação', 'description' => 'Apresentação de produtos', 'default_duration' => 60],
            ['name' => 'Negociação', 'description' => 'Negociação de condições', 'default_duration' => 90],
            ['name' => 'Suporte', 'description' => 'Apoio técnico', 'default_duration' => 45],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('calendar_actions');
    }
};