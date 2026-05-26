<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('EUR');
            $table->string('billing_period')->default('monthly');
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('status')->default('trial');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('feature');
            $table->integer('used')->default(0);
            $table->integer('limit')->nullable();
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('old_plan_id')->nullable()->constrained('plans')->onDelete('cascade');
            $table->foreignId('new_plan_id')->nullable()->constrained('plans')->onDelete('cascade');
            $table->string('action');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_logs');
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};