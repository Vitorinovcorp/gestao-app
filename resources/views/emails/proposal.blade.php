<!DOCTYPE html>
<html>
<head>
    <title>Proposta Comercial</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #6D5BD0 0%, #6B56C5 100%); padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
            <h2 style="color: white; margin: 0;">Proposta Comercial</h2>
            <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0;">{{ $deal->title }}</p>
        </div>
        
        <div style="border: 1px solid #ddd; border-top: none; padding: 20px; background: white; border-radius: 0 0 10px 10px;">
            {!! nl2br(e($messageBody)) !!}
            
            <hr style="margin: 20px 0;">
            
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                Este email foi enviado automaticamente pelo sistema de gestão. 
                Por favor, não responda diretamente a este email.
            </p>
        </div>
    </div>
</body>
</html>