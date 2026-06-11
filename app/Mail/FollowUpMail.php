<?php

namespace App\Mail;

use App\Models\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public Deal $deal;
    public array $template;

    public function __construct(Deal $deal, array $template)
    {
        $this->deal = $deal;
        $this->template = $template;
    }

    public function envelope(): Envelope
    {
        $subject = str_replace(
            '{client_name}',
            $this->deal->entity->name ?? 'Cliente',
            $this->template['subject']
        );

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $body = str_replace(
            '{client_name}',
            $this->deal->entity->name ?? 'Cliente',
            $this->template['body']
        );
        $body = str_replace(
            '{user_name}',
            $this->deal->owner->name ?? 'Equipa Comercial',
            $body
        );

        return new Content(
            view: 'emails.follow-up',
            with: [
                'bodyContent' => $body,
                'deal' => $this->deal
            ]
        );
    }
}