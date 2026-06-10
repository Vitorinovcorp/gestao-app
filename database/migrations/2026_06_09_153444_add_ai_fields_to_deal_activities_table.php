<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiFieldsToDealActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            $table->string('contact_method')->nullable()->after('user_id');
            $table->integer('duration_minutes')->nullable()->after('contact_method');
            $table->string('outcome')->nullable()->after('duration_minutes');
            $table->boolean('follow_up_needed')->default(false)->after('outcome');
            $table->date('follow_up_date')->nullable()->after('follow_up_needed');
        });
    }

    public function down()
    {
        Schema::table('deal_activities', function (Blueprint $table) {
            $table->dropColumn([
                'contact_method', 'duration_minutes', 'outcome',
                'follow_up_needed', 'follow_up_date'
            ]);
        });
    }
}