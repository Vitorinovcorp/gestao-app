<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\AISuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI;

class ProcessAISuggestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;
    public $timeout = 120;
    public $tries = 3;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        Log::info("ProcessAISuggestions iniciado para tenant: {$this->tenantId}");
        
        $deals = Deal::with(['activities', 'entity', 'owner'])
            ->where('tenant_id', $this->tenantId)
            ->where('stage', '!=', 'won')
            ->where('stage', '!=', 'lost')
            ->get();
        
        Log::info("Negócios encontrados: " . $deals->count());
        
        foreach ($deals as $deal) {
            try {
                $this->analyzeDeal($deal);
                sleep(2); // Evitar rate limit
            } catch (\Exception $e) {
                Log::error("Erro ao analisar deal {$deal->id}: " . $e->getMessage());
            }
        }
    }
    
    protected function analyzeDeal(Deal $deal)
    {
        $recentSuggestion = AISuggestion::where('deal_id', $deal->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(24))
            ->exists();
        
        if ($recentSuggestion) {
            Log::info("Deal {$deal->id} já tem sugestão recente, pulando...");
            return;
        }
        
        $context = $this->buildContext($deal);
        $daysWithoutContact = $this->getDaysWithoutContact($deal);
        $sentiment = $this->analyzeSentiment($deal);
        
        try {
            $client = OpenAI::client(config('openai.api_key'));
            
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSystemPrompt()],
                    ['role' => 'user', 'content' => $context],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);
            
            $suggestionData = $response->choices[0]->message->content;
            $parsedData = $this->parseSuggestionResponse($suggestionData);
            
            $this->createSuggestionFromData($deal, $parsedData, $daysWithoutContact, $sentiment);
            
        } catch (\Exception $e) {
            Log::error("Erro na API OpenAI para deal {$deal->id}: " . $e->getMessage());
            $this->createFallbackSuggestion($deal, $daysWithoutContact);
        }
    }
    
    protected function buildContext(Deal $deal)
    {
        $lastActivity = $deal->activities()->latest()->first();
        $lastActivityDate = $lastActivity ? $lastActivity->created_at->format('d/m/Y H:i') : 'Nenhuma atividade';
        $lastActivityDesc = $lastActivity ? $lastActivity->description : 'Nenhuma';
        
        $recentActivities = $deal->activities()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($a) {
                return "- {$a->created_at->format('d/m/Y')}: {$a->description}";
            })
            ->implode("\n");
        
        $daysWithoutContact = $this->getDaysWithoutContact($deal);
        
        return "
=== DADOS DO NEGÓCIO ===
ID: {$deal->id}
Título: {$deal->title}
Valor: €" . number_format($deal->value, 2) . "
Estágio: {$deal->stage}
Probabilidade: {$deal->probability}%
Dias sem contacto: {$daysWithoutContact}
Cliente: " . ($deal->entity->name ?? 'N/A') . "
Responsável: " . ($deal->owner->name ?? 'N/A') . "

=== ÚLTIMA ATIVIDADE ===
Data: {$lastActivityDate}
Descrição: {$lastActivityDesc}

=== ATIVIDADES RECENTES ===
{$recentActivities}

=== INSTRUÇÕES ===
1. Analise o sentimento das atividades (positivo, neutro, negativo)
2. Identifique pontos críticos ou oportunidades
3. Sugira a PRÓXIMA AÇÃO mais importante
4. Sugira uma DATA para realizar esta ação
5. Justifique sua sugestão
";
    }
    
    protected function getDaysWithoutContact(Deal $deal)
    {
        $lastActivity = $deal->activities()->latest()->first();
        if (!$lastActivity) {
            return now()->diffInDays($deal->created_at);
        }
        
        return now()->diffInDays($lastActivity->created_at);
    }
    
    protected function analyzeSentiment(Deal $deal)
    {
        $lastActivity = $deal->activities()->latest()->first();
        if (!$lastActivity) {
            return 'neutral';
        }
        
        $description = strtolower($lastActivity->description);
        
        $positiveWords = ['interessado', 'gostou', 'aprovou', 'fechar', 'avançar', 'positivo', 'otimo', 'bom'];
    
        $negativeWords = ['problema', 'reclamou', 'caro', 'duvida', 'negativo', 'ruim', 'cancelar', 'desistiu'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            if (strpos($description, $word) !== false) $positiveCount++;
        }
        
        foreach ($negativeWords as $word) {
            if (strpos($description, $word) !== false) $negativeCount++;
        }
        
        if ($positiveCount > $negativeCount) return 'positive';
        if ($negativeCount > $positiveCount) return 'negative';
        return 'neutral';
    }
    
    protected function getSystemPrompt()
    {
        return "
Você é um especialista em CRM e vendas B2B. Analise o contexto do negócio e sugira a PRÓXIMA AÇÃO mais importante.

REGRAS:
1. Seja específico e acionável
2. Priorize ações de alto impacto
3. Sugira uma data realista para execução
4. Justifique baseado no contexto
5. Detecte se o negócio está parado e sugira retomada de contacto

RESPONDA EXATAMENTE NESTE FORMATO JSON:
{
    \"type\": \"call|meeting|email|proposal|task\",
    \"title\": \"Título curto e objetivo (máx 80 caracteres)\",
    \"description\": \"Descrição detalhada do que fazer (máx 300 caracteres)\",
    \"reason\": \"Justificativa baseada na análise (máx 200 caracteres)\",
    \"suggested_date\": \"YYYY-MM-DD (data sugerida para ação)\",
    \"priority\": \"high|medium|low\"
}

EXEMPLOS:
- Se cliente demonstrou interesse mas está sem contacto há 5 dias: marcar chamada
- Se cliente pediu proposta: enviar documento em 2 dias
- Se negócio parado há 15 dias: retomar contacto urgente

NÃO INCLUA TEXTO FORA DO JSON.
";
    }
    
    protected function parseSuggestionResponse($response)
    {
        preg_match('/\{.*\}/s', $response, $matches);
        
        if (!isset($matches[0])) {
            throw new \Exception('Resposta não contém JSON válido');
        }
        
        $data = json_decode($matches[0], true);
        
        if (!$data) {
            throw new \Exception('JSON inválido');
        }
        
        return [
            'type' => $data['type'] ?? 'task',
            'title' => $data['title'] ?? 'Sugestão de ação',
            'description' => $data['description'] ?? 'Revise o negócio e tome a próxima ação.',
            'reason' => $data['reason'] ?? 'Baseado na análise automática do negócio',
            'suggested_date' => $data['suggested_date'] ?? now()->addDays(2)->format('Y-m-d'),
            'priority' => $data['priority'] ?? 'medium',
        ];
    }
    
    protected function createSuggestionFromData(Deal $deal, $data, $daysWithoutContact, $sentiment)
    {
        $suggestion = AISuggestion::create([
            'deal_id' => $deal->id,
            'user_id' => $deal->owner_id,
            'type' => $data['type'],
            'title' => $this->getSuggestionEmoji($data['type']) . ' ' . $data['title'],
            'description' => $data['description'],
            'reason' => $data['reason'],
            'status' => 'pending',
            'suggested_at' => now(),
            'days_without_contact' => $daysWithoutContact,
            'suggested_action_type' => $data['type'],
            'suggested_date' => $data['suggested_date'],
            'sentiment' => $sentiment,
            'context_data' => json_encode([
                'priority' => $data['priority'],
                'deal_stage' => $deal->stage,
                'deal_probability' => $deal->probability,
            ]),
        ]);
        
        Log::info("Sugestão criada para deal {$deal->id}: {$suggestion->title}");
        
        return $suggestion;
    }
    
    protected function getSuggestionEmoji($type)
    {
        $emojis = [
            'call' => '📞',
            'meeting' => '🤝',
            'email' => '📧',
            'proposal' => '📄',
            'task' => '✅',
        ];
        
        return $emojis[$type] ?? '💡';
    }
    
    protected function createFallbackSuggestion(Deal $deal, $daysWithoutContact)
    {
        $title = '📞 Sugestão de follow-up';
        $description = 'O negócio está sem atualização há ' . $daysWithoutContact . ' dias.';
        $reason = 'Baseado em regras automáticas de negócios parados';
        
        if ($daysWithoutContact > 14) {
            $title = '⚠️ NEGÓCIO PARADO - Ação urgente';
            $description = 'Este negócio está sem contacto há mais de 14 dias. Entre em contacto imediatamente.';
        } elseif ($daysWithoutContact > 7) {
            $description = 'O negócio está sem atualização há ' . $daysWithoutContact . ' dias. Agende uma chamada de follow-up.';
        }
        
        AISuggestion::create([
            'deal_id' => $deal->id,
            'user_id' => $deal->owner_id,
            'type' => 'task',
            'title' => $title,
            'description' => $description,
            'reason' => $reason,
            'status' => 'pending',
            'suggested_at' => now(),
            'days_without_contact' => $daysWithoutContact,
            'suggested_action_type' => 'call',
            'suggested_date' => now()->addDays(1)->format('Y-m-d'),
            'sentiment' => 'neutral',
        ]);
        
        Log::info("Fallback suggestion created for deal {$deal->id}");
    }
}