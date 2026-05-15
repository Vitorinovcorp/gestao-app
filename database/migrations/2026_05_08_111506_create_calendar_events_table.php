<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->integer('duration_minutes');
            $table->foreignId('type_id')->constrained('calendar_types');
            $table->foreignId('action_id')->constrained('calendar_actions');
            $table->foreignId('entity_id')->nullable()->constrained('entities');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('is_confirmed')->default(false);
            $table->string('share')->default('private');
            $table->string('knowledge')->default('medium');
            $table->json('reminders')->nullable();
            $table->json('attendees')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
};