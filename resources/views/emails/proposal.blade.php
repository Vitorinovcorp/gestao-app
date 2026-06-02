<!DOCTYPE html>
<html>
<head>
    <title>Proposta Comercial</title>
</head>
<body>
    <h2>Olá,</h2>
    
    @if($customMessage)
        <p>{{ $customMessage }}</p>
    @else
        <p>Segue em anexo a proposta comercial referente ao negócio "{{ $deal->title }}".</p>
    @endif
    
    <p>Qualquer dúvida, estamos à disposição.</p>
    
    <p>Atenciosamente,<br>
    {{ $deal->owner->name }}</p>
</body>
</html>