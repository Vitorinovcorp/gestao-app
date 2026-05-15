<?php

namespace App\Mail;

use App\Models\SupplierInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(SupplierInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Comprovativo de Pagamento - Fatura {$this->invoice->number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-proof',
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->invoice->payment_proof_path) {
            $attachments[] = Attachment::fromStorageDisk('private', $this->invoice->payment_proof_path);
        }
        
        return $attachments;
    }
}