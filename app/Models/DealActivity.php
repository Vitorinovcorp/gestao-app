<?php

namespace App\Models;

use App\Events\ActivityCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    protected $fillable = [
        'deal_id',
        'type',
        'description',
        'scheduled_at',
        'user_id',
        'contact_method',   
        'duration_minutes', 
        'outcome',          
        'follow_up_needed', 
        'follow_up_date',   
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'follow_up_date' => 'date',
        'duration_minutes' => 'integer',
        'follow_up_needed' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'created' => ActivityCreated::class, 
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute()
    {
        $map = [
            'call' => '📞 Chamada',
            'email' => '📧 Email',
            'meeting' => '🤝 Reunião',
            'note' => '📝 Nota',
            'invoice' => '💰 Fatura',
            'update' => '🔄 Atualização',
            'proposal' => '📄 Proposta',
            'follow_up' => '⏰ Follow-up',
        ];
        return $map[$this->type] ?? ucfirst($this->type);
    }

    public function getContactMethodLabelAttribute()
    {
        $map = [
            'phone' => '📞 Telefone',
            'email' => '📧 Email',
            'in_person' => '👥 Presencial',
            'whatsapp' => '💬 WhatsApp',
            'video_call' => '🎥 Videochamada',
        ];
        return $map[$this->contact_method] ?? $this->contact_method;
    }

    public function getOutcomeLabelAttribute()
    {
        $map = [
            'positive' => '✅ Positivo',
            'neutral' => '⚪ Neutro',
            'negative' => '❌ Negativo',
            'pending' => '⏳ Pendente',
        ];
        return $map[$this->outcome] ?? $this->outcome;
    }

    public function detectSentiment(): string
    {
        $positiveWords = ['interessado', 'gostou', 'aprovou', 'fechar', 'avançar', 'positivo', 'otimo', 'bom', 'sim', 'confirmado'];
        $negativeWords = ['problema', 'reclamou', 'caro', 'duvida', 'negativo', 'ruim', 'cancelar', 'desistiu', 'não', 'recusou'];
        
        $description = strtolower($this->description);
        
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

    public function needsFollowUp(): bool
    {
        if ($this->follow_up_needed) return true;
        
        if ($this->detectSentiment() === 'negative') return true;
        
        if ($this->type === 'proposal' && $this->outcome === 'pending') return true;
        
        $needWords = ['aguardo', 'pendente', 'depois', 'retorno', 'follow', 'acompanhar'];
        $description = strtolower($this->description);
        
        foreach ($needWords as $word) {
            if (strpos($description, $word) !== false) return true;
        }
        
        return false;
    }

    public function generateFollowUpSuggestion(): ?array
    {
        if (!$this->needsFollowUp()) {
            return null;
        }
        
        $sentiment = $this->detectSentiment();
        $daysToAdd = $sentiment === 'positive' ? 3 : ($sentiment === 'negative' ? 1 : 5);
        
        $suggestion = [
            'type' => 'follow_up',
            'title' => '⏰ Follow-up necessário',
            'description' => "Baseado na atividade do dia {$this->created_at->format('d/m/Y')}: {$this->description}",
            'suggested_date' => now()->addDays($daysToAdd)->format('Y-m-d'),
        ];
        
        if ($sentiment === 'positive') {
            $suggestion['title'] = '✅ Seguir oportunidade';
            $suggestion['description'] .= "\n\nCliente demonstrou interesse. Agende próxima reunião para avançar.";
        } elseif ($sentiment === 'negative') {
            $suggestion['title'] = '⚠️ Atenção - Cliente insatisfeito';
            $suggestion['description'] .= "\n\nCliente manifestou insatisfação. Entre em contacto o mais breve possível.";
        }
        
        return $suggestion;
    }

    public function createFollowUpActivity()
    {
        if (!$this->needsFollowUp()) {
            return null;
        }
        
        $suggestion = $this->generateFollowUpSuggestion();
        if (!$suggestion) return null;
        
        return self::create([
            'deal_id' => $this->deal_id,
            'user_id' => $this->user_id,
            'type' => 'follow_up',
            'description' => $suggestion['description'],
            'scheduled_at' => $suggestion['suggested_date'] . ' 09:00:00',
            'follow_up_needed' => false,
        ]);
    }
}