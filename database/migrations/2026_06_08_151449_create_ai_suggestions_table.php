<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // call, meeting, task, note, proposal, follow_up
            $table->string('title');
            $table->text('description');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, dismissed, archived
            $table->timestamp('suggested_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_suggestions');
    }
};