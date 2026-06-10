<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProposalFieldsToDealsTable extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('proposal_file')->nullable();
            $table->timestamp('proposal_sent_at')->nullable();
            $table->unsignedBigInteger('proposal_sent_by')->nullable();
            $table->string('proposal_sent_to')->nullable();
            $table->text('proposal_email_body')->nullable();
            $table->string('proposal_status')->default('pending');
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['proposal_file', 'proposal_sent_at', 'proposal_sent_by', 'proposal_sent_to', 'proposal_email_body', 'proposal_status']);
        });
    }
}