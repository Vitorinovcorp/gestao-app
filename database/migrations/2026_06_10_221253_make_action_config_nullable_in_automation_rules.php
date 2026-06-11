<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->json('action_config')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->json('action_config')->nullable(false)->change();
        });
    }
};