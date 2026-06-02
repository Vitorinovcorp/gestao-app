<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Pessoas (contactos individuais)
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });

        // Negócios
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained('people')->onDelete('set null');
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->string('stage')->default('lead');
            $table->integer('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });

        // Eventos de calendário (polimórfico)
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->morphs('eventable');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('location')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });

        // Attendees (pivot para eventos)
        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained('calendar_events')->onDelete('cascade');
            $table->morphs('attendee');
            $table->timestamps();
        });

        // Histórico de interações (para cronologia)
        Schema::create('deal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->string('type'); // call, task, meeting, note, email
            $table->text('description');
            $table->dateTime('scheduled_at')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_activities');
        Schema::dropIfExists('calendar_event_attendees');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('people');
    }
};