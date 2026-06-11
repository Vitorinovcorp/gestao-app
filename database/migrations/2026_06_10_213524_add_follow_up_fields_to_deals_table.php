<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'follow_up_active')) {
                $table->boolean('follow_up_active')->default(false);
            }
            if (!Schema::hasColumn('deals', 'follow_up_next_send_at')) {
                $table->timestamp('follow_up_next_send_at')->nullable();
            }
            if (!Schema::hasColumn('deals', 'follow_up_email_index')) {
                $table->integer('follow_up_email_index')->default(0);
            }
            if (!Schema::hasColumn('deals', 'follow_up_started_at')) {
                $table->timestamp('follow_up_started_at')->nullable();
            }
            if (!Schema::hasColumn('deals', 'follow_up_cancelled_at')) {
                $table->timestamp('follow_up_cancelled_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'follow_up_active',
                'follow_up_next_send_at',
                'follow_up_email_index',
                'follow_up_started_at',
                'follow_up_cancelled_at'
            ]);
        });
    }
};