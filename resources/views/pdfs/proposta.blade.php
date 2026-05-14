<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proposta {{ $proposta->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-box {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4F46E5;
            color: white;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #4F46E5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-closed { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROPOSTA COMERCIAL</h1>
        <p>Nº {{ $proposta->number }}</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Cliente:</span>
            <span>{{ $proposta->client->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">NIF:</span>
            <span>{{ $proposta->client->nif }}</span>
        </div>
        <div class="info-row">
            <span class="label">Email:</span>
            <span>{{ $proposta->client->email ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Telefone:</span>
            <span>{{ $proposta->client->mobile ?? $proposta->client->phone ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Morada:</span>
            <span>{{ $proposta->client->address ?: '-' }}</span>
        </div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Data da Proposta:</span>
            <span>{{ \Carbon\Carbon::parse($proposta->proposal_date)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Data de Validade:</span>
            <span>{{ \Carbon\Carbon::parse($proposta->validity)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status:</span>
            <span class="status status-{{ $proposta->status }}">
                {{ $proposta->status == 'closed' ? 'Fechada' : ($proposta->status == 'sent' ? 'Enviada' : 'Rascunho') }}
            </span>
        </div>
    </div>

    <div class="title">Itens da Proposta</div>
    
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th width="80">Quantidade</th>
                <th width="100">Preço Unit.</th>
                <th width="100">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposta->lines as $line)
            <tr>
                <td>{{ $line->article->name ?? 'Produto' }}</td>
                <td class="text-right">{{ $line->quantity }}</td>
                <td class="text-right">{{ number_format($line->unit_price, 2) }} €</td>
                <td class="text-right">{{ number_format($line->quantity * $line->unit_price, 2) }} €</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Nenhum item encontrado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5;">
                <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($proposta->total_value, 2) }} €</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($proposta->notes)
    <div class="info-box">
        <div class="label">Observações:</div>
        <div style="margin-top: 5px;">{{ $proposta->notes }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Este documento foi gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>