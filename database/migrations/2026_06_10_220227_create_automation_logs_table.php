<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('automation_rule_id');
            $table->unsignedBigInteger('deal_id');
            $table->string('status'); // success, failed, skipped
            $table->text('message')->nullable();
            $table->unsignedBigInteger('created_activity_id')->nullable();
            $table->timestamps();
            
            $table->foreign('automation_rule_id')->references('id')->on('automation_rules')->onDelete('cascade');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('automation_logs');
    }
};