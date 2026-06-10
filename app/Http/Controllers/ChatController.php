<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Entity;
use App\Models\User;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function chat(Request $request)
    {
        $user = auth()->user();
        $tenant = tenant();

        if (!$user->hasPermissionTo('view_chat')) {
            return response()->json(['error' => 'Não tem permissão para usar o chat.'], 403);
        }

        $message = $request->input('message');
        $context = $this->getCRMContext($tenant);

        $quickResponse = $this->getQuickResponse($message, $tenant);

        if ($quickResponse) {
            return response()->json([
                'answer' => $quickResponse['answer'],
                'action' => $quickResponse['action'] ?? null
            ]);
        }

        $systemPrompt = "
Você é um assistente de CRM especializado. Responda apenas com os dados disponíveis no contexto.
Mantenha as respostas curtas e diretas, em português.
Se não souber, diga que não tem informação.

Contexto do CRM:
- Utilizadores: {$context['users']}
- Negócios: {$context['deals']}
- Entidades: {$context['entities']}
- Eventos: {$context['events']}
";

        try {
            $client = OpenAI::client(config('openai.api_key'));

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
            ]);

            $answer = $response->choices[0]->message->content;
            $this->saveChatHistory($user->id, $message, $answer);
            $action = $this->detectAction($answer);

            return response()->json([
                'answer' => $answer,
                'action' => $action,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no chat: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar a pergunta.'], 500);
        }
    }

    protected function getQuickResponse($message, $tenant)
    {
        $messageLower = strtolower($message);

        // Volume em Negociação
        if (
            str_contains($messageLower, 'volume em negociação') ||
            (str_contains($messageLower, 'volume') && str_contains($messageLower, 'negociação'))
        ) {
            $deals = Deal::where('tenant_id', $tenant->id)
                ->whereIn('stage', ['negotiation', 'proposal', 'qualification'])
                ->get();

            $totalValue = $deals->sum('value');
            $totalCount = $deals->count();
            $wonValue = Deal::where('tenant_id', $tenant->id)->where('stage', 'won')->sum('value');

            $answer = "📊 **Volume em Negociação**\n\n" .
                       "📌 Total de negócios ativos: **{$totalCount}**\n" .
                       "💰 Valor total do pipeline: **€" . number_format($totalValue, 2) . "**\n" .
                       "📈 Ticket médio: **€" . number_format($totalCount > 0 ? $totalValue / $totalCount : 0, 2) . "**\n" .
                       "🏆 Total já fechado (Won): **€" . number_format($wonValue, 2) . "**\n\n";

            if ($totalCount > 0) {
                $answer .= "📊 **Negócios ativos:**\n";
                foreach ($deals->take(5) as $deal) {
                    $answer .= "   • {$deal->title} - €" . number_format($deal->value, 2) . "\n";
                }
                if ($deals->count() > 5) {
                    $answer .= "   • ... e mais " . ($deals->count() - 5) . " negócios\n";
                }
            }

            return ['answer' => $answer];
        }

        if (
            str_contains($messageLower, 'clientes mais ativos') ||
            (str_contains($messageLower, 'clientes') && str_contains($messageLower, 'ativos')) ||
            str_contains($messageLower, 'top clientes')
        ) {
            try {
                $topClients = Entity::where('tenant_id', $tenant->id)
                    ->whereHas('deals', function($q) {
                        $q->whereIn('stage', ['negotiation', 'proposal', 'won']);
                    })
                    ->withCount(['deals' => function($q) {
                        $q->whereIn('stage', ['negotiation', 'proposal', 'won']);
                    }])
                    ->orderBy('deals_count', 'desc')
                    ->limit(5)
                    ->get();

                if ($topClients->isEmpty()) {
                    $topClients = Entity::where('tenant_id', $tenant->id)
                        ->has('deals')
                        ->withCount('deals')
                        ->orderBy('deals_count', 'desc')
                        ->limit(5)
                        ->get();
                }

                if ($topClients->isEmpty()) {
                    return ['answer' => "👥 Nenhum cliente com negócios ativos no momento. Crie alguns negócios para começar!"];
                }

                $answer = "👥 **Top Clientes mais ativos**\n\n";
                foreach ($topClients as $index => $client) {
                    $totalDeals = $client->deals_count;
                    $totalValue = $client->deals->sum('value');
                    $answer .= ($index + 1) . ". **{$client->name}**\n";
                    $answer .= "   📊 {$totalDeals} negócio(s) | 💰 €" . number_format($totalValue, 2) . "\n\n";
                }

                return ['answer' => $answer];

            } catch (\Exception $e) {
                Log::error('Erro ao buscar clientes ativos: ' . $e->getMessage());

                $allClients = Entity::where('tenant_id', $tenant->id)
                    ->limit(5)
                    ->get();

                if ($allClients->isEmpty()) {
                    return ['answer' => "👥 Nenhum cliente cadastrado no momento."];
                }

                $answer = "👥 **Lista de Clientes**\n\n";
                foreach ($allClients as $index => $client) {
                    $answer .= ($index + 1) . ". **{$client->name}**\n";
                    $answer .= "   📧 {$client->email} | 📞 {$client->phone}\n\n";
                }

                return ['answer' => $answer];
            }
        }

        if (
            str_contains($messageLower, 'negócios em follow up') ||
            str_contains($messageLower, 'follow up') ||
            str_contains($messageLower, 'follow-up')
        ) {
            $followUpDeals = Deal::where('tenant_id', $tenant->id)
                ->whereIn('stage', ['negotiation', 'proposal', 'qualification'])
                ->limit(10)
                ->get();

            if ($followUpDeals->isEmpty()) {
                return ['answer' => "⏰ Nenhum negócio ativo no momento."];
            }

            $answer = "⏰ **Negócios que precisam de follow-up**\n\n";
            foreach ($followUpDeals as $deal) {
                $lastActivity = $deal->activities()->latest()->first();
                if ($lastActivity) {
                    $daysSince = (int) abs(now()->diffInDays($lastActivity->created_at));
                    if ($daysSince == 0) {
                        $daysText = 'Hoje';
                    } elseif ($daysSince == 1) {
                        $daysText = 'Ontem';
                    } else {
                        $daysText = "Há {$daysSince} dias";
                    }
                } else {
                    $daysText = 'Sem atividade registrada';
                }
                $answer .= "• **{$deal->title}** - Último contato: {$daysText}\n";
            }
            $answer .= "\n💡 Sugiro entrar em contato com estes clientes em breve.";

            return ['answer' => $answer];
        }

        return null;
    }

    protected function getCRMContext($tenant)
    {
        if (!$tenant) {
            return [
                'users' => 'Nenhum tenant ativo',
                'deals' => 'Nenhum negócio disponível',
                'entities' => 'Nenhuma entidade disponível',
                'events' => 'Nenhum evento disponível',
            ];
        }

        $users = User::where('tenant_id', $tenant->id)
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($u) {
                return "ID:{$u->id} - {$u->name} ({$u->email})";
            })
            ->implode("\n");

        $deals = Deal::where('tenant_id', $tenant->id)
            ->select('id', 'title', 'value', 'stage', 'entity_id')
            ->with('entity:id,name')
            ->get()
            ->map(function ($d) {
                $clientName = $d->entity->name ?? 'N/A';
                return "ID:{$d->id} - {$d->title} (Valor: {$d->value}, Estado: {$d->stage}, Cliente: {$clientName})";
            })
            ->implode("\n");

        $entities = Entity::where('tenant_id', $tenant->id)
            ->select('id', 'name', 'type', 'email', 'phone')
            ->get()
            ->map(function ($e) {
                return "ID:{$e->id} - {$e->name} ({$e->type}, Email: {$e->email}, Telefone: {$e->phone})";
            })
            ->implode("\n");

        $events = CalendarEvent::where('tenant_id', $tenant->id)
            ->select('id', 'title', 'start_datetime', 'user_id')
            ->get()
            ->map(function ($e) {
                return "ID:{$e->id} - {$e->title} (Data: {$e->start_datetime}, Responsável: {$e->user_id})";
            })
            ->implode("\n");

        return [
            'users' => $users,
            'deals' => $deals,
            'entities' => $entities,
            'events' => $events,
        ];
    }

    protected function saveChatHistory($userId, $question, $answer)
    {
        Log::info("Chat: {$userId} perguntou: {$question} - Resposta: {$answer}");
    }

    protected function detectAction($answer)
    {
        $action = null;

        if (preg_match('/ID:\s*(\d+)/', $answer, $matches)) {
            $id = $matches[1];
            $action = ['type' => 'open_deal', 'id' => $id];
        } elseif (preg_match('/cliente\s+(\d+)/i', $answer, $matches)) {
            $id = $matches[1];
            $action = ['type' => 'open_entity', 'id' => $id];
        }

        return $action;
    }
}