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
            $table->string('trigger_type'); // inactivity_days, stage_change, etc.
            $table->json('conditions'); // { "days": 5, "stage": "follow_up" }
            $table->string('action_type'); // create_activity, send_notification, etc.
            $table->json('action_config'); // { "activity_type": "task", "priority": "high" }
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('automation_rules');
    }
};