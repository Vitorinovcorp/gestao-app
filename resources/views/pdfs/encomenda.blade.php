<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Encomenda {{ $encomenda->number }}</title>
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
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ENCOMENDA</h1>
        <p>Nº {{ $encomenda->number }}</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Cliente:</span>
            <span>{{ $encomenda->client->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">NIF:</span>
            <span>{{ $encomenda->client->nif }}</span>
        </div>
        <div class="info-row">
            <span class="label">Email:</span>
            <span>{{ $encomenda->client->email ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Telefone:</span>
            <span>{{ $encomenda->client->mobile ?? $encomenda->client->phone ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Morada:</span>
            <span>{{ $encomenda->client->address ?: '-' }}</span>
        </div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Data da Encomenda:</span>
            <span>{{ \Carbon\Carbon::parse($encomenda->order_date)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status:</span>
            <span class="status status-{{ $encomenda->status }}">
                {{ ucfirst($encomenda->status) }}
            </span>
        </div>
        @if($encomenda->expected_delivery)
        <div class="info-row">
            <span class="label">Entrega Prevista:</span>
            <span>{{ \Carbon\Carbon::parse($encomenda->expected_delivery)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>

    <div class="title">Itens da Encomenda</div>
    
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th width="60">Qtd</th>
                <th width="80">Preço Unit.</th>
                <th width="50">IVA%</th>
                <th width="80">Valor IVA</th>
                <th width="80">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSemIva = 0;
                $totalIva = 0;
            @endphp
            @forelse($encomenda->lines as $line)
            @php
                $subtotal = $line->quantity * $line->unit_price;
                $valorIva = $subtotal * ($line->vat_rate / 100);
                $totalSemIva += $subtotal;
                $totalIva += $valorIva;
            @endphp
            <tr>
                <td>{{ $line->article->name ?? 'Produto' }}</td>
                <td class="text-right">{{ $line->quantity }}</td>
                <td class="text-right">€ {{ number_format($line->unit_price, 2) }}</td>
                <td class="text-right">{{ $line->vat_rate }}%</td>
                <td class="text-right">€ {{ number_format($valorIva, 2) }}</td>
                <td class="text-right">€ {{ number_format($subtotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Nenhum item encontrado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5;">
                <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                <td colspan="2" class="text-right"><strong>€ {{ number_format($totalSemIva, 2) }}</strong></td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <td colspan="4" class="text-right"><strong>IVA Total:</strong></td>
                <td colspan="2" class="text-right"><strong>€ {{ number_format($totalIva, 2) }}</strong></td>
            </tr>
            <tr style="background-color: #e5e5e5;">
                <td colspan="4" class="text-right"><strong>TOTAL FINAL:</strong></td>
                <td colspan="2" class="text-right"><strong>€ {{ number_format($totalSemIva + $totalIva, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($encomenda->notes)
    <div class="info-box">
        <div class="label">Observações:</div>
        <div style="margin-top: 5px;">{{ $encomenda->notes }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Este documento foi gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>O valor final inclui IVA à taxa legal em vigor.</p>
    </div>
</body>
</html>