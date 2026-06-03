<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\DealActivity;
use App\Emails\FollowUpTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFollowUpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    public function handle()
    {
        // Verificar se o follow-up ainda está ativo
        if (!$this->deal->follow_up_active) {
            return;
        }

        // Verificar se está dentro do horário de trabalho (09:00-18:00)
        $hour = now()->hour;
        if ($hour < 9 || $hour >= 18) {
            // Reagendar para o próximo horário útil
            $nextSend = now()->startOfDay()->addHours(9);
            $this->deal->update(['follow_up_next_send_at' => $nextSend]);
            return;
        }

        // Obter o email do cliente
        $email = $this->deal->entity->email ?? null;
        if (!$email) {
            $this->deal->update(['follow_up_active' => false]);
            return;
        }

        // Escolher o template
        $template = FollowUpTemplates::get($this->deal->follow_up_email_index);

        // Enviar email
        Mail::raw($template, function ($message) use ($email) {
            $message->to($email)
                ->subject('Follow-up da Proposta');
        });

        // Registrar no histórico
        DealActivity::create([
            'deal_id' => $this->deal->id,
            'type' => 'email',
            'description' => "Email de follow-up enviado: {$template}",
            'user_id' => $this->deal->owner_id,
            'scheduled_at' => now(),
        ]);

        // Atualizar o índice para o próximo email
        $this->deal->update([
            'follow_up_email_index' => $this->deal->follow_up_email_index + 1,
            'follow_up_next_send_at' => now()->addDays(2),
        ]);
    }
}