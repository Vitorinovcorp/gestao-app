<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
        });
    }

    public function down()
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            $table->dropColumn(['subject', 'body', 'metadata', 'icon', 'color']);
        });
    }
};