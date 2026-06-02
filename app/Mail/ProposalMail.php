<?php

namespace App\Mail;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deal;
    public $customMessage;

    public function __construct(Deal $deal, $customMessage = null)
    {
        $this->deal = $deal;
        $this->customMessage = $customMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proposta Comercial - ' . $this->deal->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.proposal',
        );
    }

    public function attachments(): array
    {
        if ($this->deal->proposal_file) {
            return [
                Attachment::fromStorage('public/' . $this->deal->proposal_file),
            ];
        }
        return [];
    }
}