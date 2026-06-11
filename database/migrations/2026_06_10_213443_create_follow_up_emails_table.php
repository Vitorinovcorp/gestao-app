<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('follow_up_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('user_id');
            $table->string('email_subject');
            $table->text('email_body');
            $table->string('status')->default('sent'); // sent, failed, replied
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('follow_up_emails');
    }
};