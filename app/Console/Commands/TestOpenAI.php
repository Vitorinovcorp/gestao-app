<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI;
use App\Models\User;
use App\Models\Deal;

class TestOpenAI extends Command
{
    protected $signature = 'test:openai';
    protected $description = 'Test OpenAI integration';

    public function handle()
    {
        $this->newLine();
        $this->line('═══════════════════════════════════════════════');
        $this->info('        TESTE INTEGRAÇÃO OPENAI CRM');
        $this->line('═══════════════════════════════════════════════');
        $this->newLine();

        $this->info('📋 1. TESTE DE CONFIGURAÇÃO');
        $this->line('───────────────────────────────────────────────');

        $apiKey = config('openai.api_key');
        if ($apiKey) {
            $this->line('✅ OPENAI_API_KEY: Configurada');
            $this->line('   Prefixo: ' . substr($apiKey, 0, 15) . '...');
            $this->line('   Tamanho: ' . strlen($apiKey) . ' caracteres');
        } else {
            $this->error('❌ OPENAI_API_KEY: NÃO configurada no .env');
            return 1;
        }

        $timeout = config('openai.request_timeout', 30);
        $this->line('✅ Timeout: ' . $timeout . ' segundos');

        $this->newLine();
        $this->info('🌐 2. TESTE API OPENAI');
        $this->line('───────────────────────────────────────────────');

        try {
            $this->line('⏳ Enviando requisição de teste...');

            $client = OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um assistente de CRM. Responda de forma concisa.'],
                    ['role' => 'user', 'content' => 'Diga "API OpenAI funcionando!" em português']
                ],
                'max_tokens' => 50,
                'temperature' => 0.3,
            ]);

            $answer = $response->choices[0]->message->content;
            $this->line('✅ Resposta recebida:');
            $this->line('   "' . $answer . '"');
            $this->line('✅ Tokens utilizados: ' . $response->usage->totalTokens);
            $this->line('✅ Modelo: ' . $response->model);
        } catch (\Exception $e) {
            $this->error('❌ ERRO na API OpenAI:');
            $this->error('   ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('💾 3. TESTE BANCO DE DADOS CRM');
        $this->line('───────────────────────────────────────────────');

        try {
            $tenant = tenant();

            if (!$tenant) {
                $this->warn('⚠️ Nenhum tenant no contexto, buscando primeiro tenant ativo...');
                $tenant = \App\Models\Tenant::where('is_active', 1)->first();

                if ($tenant) {
                    $this->info('✅ Tenant encontrado: ' . $tenant->name . ' (ID: ' . $tenant->id . ')');
                }
            }

            if ($tenant) {
                $userCount = User::where('tenant_id', $tenant->id)->count();
                $this->line('✅ Usuários: ' . $userCount);

                $dealCount = Deal::where('tenant_id', $tenant->id)->count();
                $this->line('✅ Negócios: ' . $dealCount);

                $entityCount = \App\Models\Entity::where('tenant_id', $tenant->id)->count();
                $this->line('✅ Entidades: ' . $entityCount);

                $eventCount = \App\Models\CalendarEvent::where('tenant_id', $tenant->id)->count();
                $this->line('✅ Eventos: ' . $eventCount);

                if ($dealCount > 0) {
                    $this->newLine();
                    $this->line('📊 Amostra de negócios:');
                    $deals = Deal::where('tenant_id', $tenant->id)
                        ->limit(3)
                        ->get();

                    foreach ($deals as $deal) {
                        $this->line("   • ID:{$deal->id} - {$deal->title} (Valor: {$deal->value})");
                    }
                } else {
                    $this->warn('⚠️ Nenhum negócio encontrado para este tenant');
                }
            } else {
                $this->error('❌ Nenhum tenant ativo encontrado no banco de dados!');
                $this->info('   Execute no Tinker: App\Models\Tenant::create([...])');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro no banco de dados: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎯 4. TESTE COMPLETO (CRM Contexto)');
        $this->line('───────────────────────────────────────────────');

        try {
            $context = $this->getCRMContext($tenant);
            $question = "Quantos negócios existem no sistema?";

            $this->line('⏳ Pergunta: ' . $question);

            $client = OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => "Você é um assistente de CRM. Responda baseado APENAS no contexto fornecido. Responda em português. Contexto: {$context}"],
                    ['role' => 'user', 'content' => $question],
                ],
                'max_tokens' => 150,
                'temperature' => 0.3,
            ]);

            $answer = $response->choices[0]->message->content;
            $this->line('✅ Resposta do assistente:');
            $this->line('   ' . $answer);
        } catch (\Exception $e) {
            $this->error('❌ Erro no teste completo: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('⚙️ 5. VERIFICANDO SUGESTÕES AI');
        $this->line('───────────────────────────────────────────────');

        $pendingSuggestions = \App\Models\AISuggestion::where('status', 'pending')->count();
        $this->line("✅ Sugestões pendentes: {$pendingSuggestions}");

        $completedSuggestions = \App\Models\AISuggestion::where('status', 'completed')->count();
        $this->line("✅ Sugestões concluídas: {$completedSuggestions}");

        $this->newLine();
        $this->line('═══════════════════════════════════════════════');
        $this->info('✅ TESTE FINALIZADO COM SUCESSO!');
        $this->line('═══════════════════════════════════════════════');
        $this->newLine();

        return 0;
    }

    protected function getCRMContext($tenant)
    {
        if (!$tenant) {
            return "Nenhum tenant disponível";
        }

        $dealCount = Deal::where('tenant_id', $tenant->id)->count();
        $userCount = User::where('tenant_id', $tenant->id)->count();

        return "Total de negócios: {$dealCount}. Total de usuários: {$userCount}.";
    }
}
