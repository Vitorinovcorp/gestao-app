<?php

namespace App\Emails;

class FollowUpTemplates
{
    public static $templates = [
        "Olá, gostaria de saber se precisa de alguma ajuda com a proposta que enviei.",
        "Bom dia! Alguma novidade sobre a proposta? Estou à disposição para esclarecer qualquer dúvida.",
        "Espero que esteja tudo bem. Aproveito para perguntar se teve oportunidade de analisar a proposta.",
        "Olá, só uma mensagem rápida para saber se precisa de mais alguma informação sobre a proposta.",
        "Bom dia! Alguma questão sobre a proposta? Posso ajudar com o que precisar.",
        "Espero que esteja tudo bem. Gostaria de saber se há alguma atualização sobre a proposta.",
        "Olá, como está? Preciso de saber se a proposta vai avançar ou se precisa de mais algum esclarecimento.",
        "Bom dia! Apenas a lembrar que estou disponível para qualquer questão sobre a proposta.",
        "Olá, espero que tenha tido oportunidade de analisar a proposta. Alguma dúvida?",
        "Bom dia! Estou a fazer um follow-up da proposta. Precisa de mais alguma informação?"
    ];

    public static function get($index)
    {
        return self::$templates[$index % count(self::$templates)];
    }
}