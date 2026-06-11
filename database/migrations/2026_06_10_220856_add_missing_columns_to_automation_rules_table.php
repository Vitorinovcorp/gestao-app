<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_rules', 'action_config')) {
                $table->json('action_config')->nullable()->after('action_type');
            }
            if (!Schema::hasColumn('automation_rules', 'inactivity_days')) {
                $table->integer('inactivity_days')->default(5)->after('trigger_type');
            }
            if (!Schema::hasColumn('automation_rules', 'activity_type')) {
                $table->string('activity_type')->default('task')->after('action_config');
            }
            if (!Schema::hasColumn('automation_rules', 'activity_priority')) {
                $table->string('activity_priority')->default('medium')->after('activity_type');
            }
            if (!Schema::hasColumn('automation_rules', 'send_notification')) {
                $table->boolean('send_notification')->default(true)->after('activity_priority');
            }
            if (!Schema::hasColumn('automation_rules', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down()
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->dropColumn([
                'action_config',
                'inactivity_days',
                'activity_type',
                'activity_priority',
                'send_notification',
                'description'
            ]);
        });
    }
};