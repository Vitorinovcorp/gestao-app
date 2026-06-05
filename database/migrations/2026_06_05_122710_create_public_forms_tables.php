<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('public_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('fields');
            $table->string('embed_code')->unique();
            $table->string('confirmation_message')->default('Obrigado! O seu formulário foi submetido com sucesso.');
            $table->boolean('is_active')->default(true);
            $table->string('success_url')->nullable();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_form_id')->constrained('public_forms')->onDelete('cascade');
            $table->json('data');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('public_forms');
    }
};