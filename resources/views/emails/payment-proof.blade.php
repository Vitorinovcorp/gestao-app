<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprovativo de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #4F46E5; }
        .logo { max-width: 150px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding-top: 20px; font-size: 12px; color: #999; border-top: 1px solid #eee; }
        .invoice-details { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .button { display: inline-block; padding: 10px 20px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Comprovativo de Pagamento</h1>
        </div>
        
        <div class="content">
            <p>Estimado(a) {{ $invoice->supplier->name }},</p>
            
            <p>Enviamos em anexo o comprovativo de pagamento da fatura <strong>{{ $invoice->number }}</strong>.</p>
            
            <div class="invoice-details">
                <p><strong>Detalhes da Fatura:</strong></p>
                <p>Número: {{ $invoice->number }}<br>
                Data de Emissão: {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                Data de Vencimento: {{ $invoice->due_date->format('d/m/Y') }}<br>
                Valor Total: € {{ number_format($invoice->total_value, 2, ',', '.') }}</p>
            </div>
            
            <p>Qualquer questão, entre em contacto connosco.</p>
            
            <p>Cumprimentos,<br>
            <strong>{{ config('app.name') }}</strong></p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>