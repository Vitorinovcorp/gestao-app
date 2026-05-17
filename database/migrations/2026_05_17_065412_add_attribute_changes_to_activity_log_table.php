<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Adicionar colunas que faltam na versão 5.0.0
            if (!Schema::hasColumn('activity_log', 'attribute_changes')) {
                $table->text('attribute_changes')->nullable();
            }
            if (!Schema::hasColumn('activity_log', 'event')) {
                $table->string('event')->nullable();
            }
            if (!Schema::hasColumn('activity_log', 'batch_uuid')) {
                $table->string('batch_uuid')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['attribute_changes', 'event', 'batch_uuid']);
        });
    }
};