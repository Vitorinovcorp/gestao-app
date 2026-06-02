<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            // Arquivo da proposta
            $table->string('proposal_file')->nullable();
            
            // Metadados do envio
            $table->timestamp('proposal_sent_at')->nullable();
            $table->foreignId('proposal_sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('proposal_sent_to')->nullable(); // email do destinatário
            $table->text('proposal_email_body')->nullable(); // corpo do email enviado
            
            // Status da proposta
            $table->string('proposal_status')->default('pending'); // pending, sent, viewed, accepted, rejected
            $table->timestamp('proposal_viewed_at')->nullable();
            $table->timestamp('proposal_accepted_at')->nullable();
            $table->timestamp('proposal_rejected_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['proposal_sent_by']);
            $table->dropColumn([
                'proposal_file',
                'proposal_sent_at',
                'proposal_sent_by',
                'proposal_sent_to',
                'proposal_email_body',
                'proposal_status',
                'proposal_viewed_at',
                'proposal_accepted_at',
                'proposal_rejected_at'
            ]);
        });
    }
};