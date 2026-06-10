<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuggestionFeaturesToAiSuggestionsTable extends Migration
{
    public function up()
    {
        Schema::table('ai_suggestions', function (Blueprint $table) {
            $table->json('context_data')->nullable()->after('metadata'); // Dados do contexto usado
            $table->integer('days_without_contact')->nullable()->after('context_data'); // Dias sem contacto
            $table->string('suggested_action_type')->nullable()->after('days_without_contact'); // call, meeting, email, proposal
            $table->date('suggested_date')->nullable()->after('suggested_action_type'); // Data sugerida para ação
            $table->string('sentiment')->nullable()->after('suggested_date'); // positive, neutral, negative
            $table->enum('user_feedback', ['accepted', 'ignored', 'dismissed', 'completed'])->nullable()->after('status');
            $table->timestamp('feedback_at')->nullable()->after('user_feedback');
            $table->timestamp('converted_to_activity_at')->nullable()->after('feedback_at'); // Quando foi convertido em atividade
            $table->unsignedBigInteger('converted_activity_id')->nullable()->after('converted_to_activity_at'); // ID da atividade criada
        });
    }

    public function down()
    {
        Schema::table('ai_suggestions', function (Blueprint $table) {
            $table->dropColumn([
                'context_data', 'days_without_contact', 'suggested_action_type',
                'suggested_date', 'sentiment', 'user_feedback', 'feedback_at',
                'converted_to_activity_at', 'converted_activity_id'
            ]);
        });
    }
}