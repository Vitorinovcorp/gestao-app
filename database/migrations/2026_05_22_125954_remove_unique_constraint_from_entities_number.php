<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropUnique(['number']);
        });
    }

    public function down()
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->unique('number');
        });
    }
};