<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type')->default('inactivity'); // inactivity, stage_change, etc
            $table->integer('inactivity_days')->default(5); // dias sem atividade
            $table->json('conditions')->nullable(); // condições adicionais
            $table->string('action_type')->default('create_activity'); // create_activity, send_email, etc
            $table->string('activity_type')->default('task'); // call, task, meeting, note
            $table->string('activity_priority')->default('medium'); // high, medium, low
            $table->boolean('send_notification')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('automation_rules');
    }
};