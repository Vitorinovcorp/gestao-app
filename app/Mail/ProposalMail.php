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

    public Deal $deal;
    public $customMessage;

    public function __construct(Deal $deal, $customMessage = null)
    {
        $this->deal = $deal;
        $this->customMessage = $customMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Proposta Comercial - {$this->deal->title}",
        );
    }

    public function content(): Content
    {
        $defaultMessage = "Prezado(a) " . ($this->deal->entity->name ?? 'Cliente') . ",\n\n";
        $defaultMessage .= "Segue em anexo a proposta comercial referente ao projeto \"{$this->deal->title}\".\n\n";
        $defaultMessage .= "Fico à disposição para esclarecer qualquer dúvida.\n\n";
        $defaultMessage .= "Atenciosamente,\n" . (auth()->user()->name ?? 'Equipa Comercial');

        return new Content(
            view: 'emails.proposal',
            with: [
                'messageBody' => $this->customMessage ?? $defaultMessage,
                'deal' => $this->deal
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->deal->proposal_file && file_exists(storage_path('app/public/' . $this->deal->proposal_file))) {
            $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->deal->proposal_file));
        }
        
        return $attachments;
    }
}