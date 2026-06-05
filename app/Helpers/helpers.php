<?php

if (!function_exists('translateStage')) {
    function translateStage($stage)
    {
        $map = [
            'won' => 'Ganho',
            'lost' => 'Perdido',
            'lead' => 'Potencial',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'follow_up' => 'Atualização'
        ];
        return $map[$stage] ?? ucfirst($stage);
    }
}