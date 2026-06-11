<!DOCTYPE html>
<html>
<head>
    <title>Follow-up</title>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #f3f4f6; padding: 15px; text-align: center; border-radius: 10px 10px 0 0;">
            <h2 style="margin: 0;">Acompanhamento</h2>
            <p style="margin: 5px 0 0; font-size: 12px;">{{ $deal->title }}</p>
        </div>
        
        <div style="border: 1px solid #ddd; border-top: none; padding: 20px; background: white; border-radius: 0 0 10px 10px;">
            {!! nl2br(e($bodyContent)) !!}
            
            <hr style="margin: 20px 0;">
            
            <p style="font-size: 12px; color: #999;">
                Este email foi enviado automaticamente. 
                Se preferir não receber mais estes acompanhamentos, 
                <a href="{{ route('deals.cancel-follow-up', $deal->id) }}">clique aqui</a>.
            </p>
        </div>
    </div>
</body>
</html>