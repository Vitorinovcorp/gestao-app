<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\FollowUpEmail;
use App\Mail\FollowUpMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessFollowUpEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    public function __construct($tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        $query = Deal::with(['entity', 'owner'])
            ->where('follow_up_active', true)
            ->whereNotNull('follow_up_next_send_at')
            ->where('follow_up_next_send_at', '<=', now());

        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $deals = $query->get();

        foreach ($deals as $deal) {
            $this->processDealFollowUp($deal);
            sleep(1); // Evitar rate limit
        }
    }

    protected function processDealFollowUp(Deal $deal)
    {
        // Verificar se ainda está em follow_up
        if ($deal->stage !== 'follow_up') {
            $deal->follow_up_active = false;
            $deal->save();
            return;
        }

        // Verificar horário comercial (9h-18h, dias úteis)
        if (!$this->isBusinessHours()) {
            return;
        }

        $emailTemplates = $this->getEmailTemplates();
        $template = $emailTemplates[$deal->follow_up_email_index % count($emailTemplates)];

        try {
            Mail::to($deal->entity->email ?? $deal->owner->email)
                ->send(new FollowUpMail($deal, $template));

            // Registrar envio
            FollowUpEmail::create([
                'deal_id' => $deal->id,
                'user_id' => $deal->owner_id,
                'email_subject' => $template['subject'],
                'email_body' => $template['body'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Atualizar próximo envio (2 dias depois, no próximo horário comercial)
            $nextSend = $this->getNextBusinessDate(now()->addDays(2));
            $deal->follow_up_next_send_at = $nextSend;
            $deal->follow_up_email_index++;
            $deal->save();

            Log::info("Follow-up email enviado para deal {$deal->id}");

        } catch (\Exception $e) {
            Log::error("Erro ao enviar follow-up email: " . $e->getMessage());
        }
    }

    protected function getEmailTemplates(): array
    {
        return [
            [
                'subject' => 'Acompanhamento - Proposta Comercial',
                'body' => "Olá {client_name},\n\nPassando para saber se teve oportunidade de analisar a nossa proposta. Estou à disposição para esclarecer qualquer dúvida.\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Dúvidas sobre a proposta?',
                'body' => "Olá {client_name},\n\nAlguma dúvida sobre a proposta que enviamos? Posso ajudar com informações adicionais.\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Avanços no projeto',
                'body' => "Olá {client_name},\n\nGostaria de saber se há novidades sobre o projeto que discutimos. Podemos agendar uma breve conversa?\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Alinhamento de expectativas',
                'body' => "Olá {client_name},\n\nApenas um lembrete para alinharmos as expectativas sobre a proposta enviada. Quando seria um bom momento para conversarmos?\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Precisa de ajuda?',
                'body' => "Olá {client_name},\n\nPrecisa de alguma ajuda adicional para avaliar a nossa proposta? Estou aqui para ajudar.\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Feedback sobre a proposta',
                'body' => "Olá {client_name},\n\nSeu feedback sobre a proposta é muito importante para nós. Poderia compartilhar sua opinião?\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Próximos passos',
                'body' => "Olá {client_name},\n\nGostaria de discutir os próximos passos do nosso projeto. Tem disponibilidade para uma rápida conversa esta semana?\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Acompanhamento - Semana',
                'body' => "Olá {client_name},\n\nComo está o andamento da análise da nossa proposta? Fico à disposição para agilizar o que for necessário.\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Oportunidade de negócio',
                'body' => "Olá {client_name},\n\nAcredito que nossa solução pode trazer grandes benefícios para sua empresa. Podemos marcar uma reunião para detalharmos melhor?\n\nAtenciosamente,\n{user_name}"
            ],
            [
                'subject' => 'Aguardando retorno',
                'body' => "Olá {client_name},\n\nEstou aguardando seu retorno sobre a proposta. Caso precise de mais informações, estou à disposição.\n\nAtenciosamente,\n{user_name}"
            ],
        ];
    }

    protected function isBusinessHours(): bool
    {
        $now = now();
        $hour = (int) $now->format('H');
        $isWeekday = !$now->isWeekend();
        $isBusinessHour = $hour >= 9 && $hour < 18;

        return $isWeekday && $isBusinessHour;
    }

    protected function getNextBusinessDate($date)
    {
        $date = \Carbon\Carbon::parse($date);
        
        while ($date->isWeekend()) {
            $date->addDay();
        }
        
        $date->hour = 10;
        $date->minute = 0;
        
        return $date;
    }
}