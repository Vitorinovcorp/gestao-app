<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teste Proposta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8 bg-gray-100">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Teste Criar Proposta</h1>
        
        <form id="formTeste">
            <div class="mb-3">
                <label class="block">Cliente ID</label>
                <input type="number" id="client_id" value="1" class="w-full p-2 border rounded">
            </div>
            <div class="mb-3">
                <label class="block">Artigo ID</label>
                <input type="number" id="article_id" value="1" class="w-full p-2 border rounded">
            </div>
            <div class="mb-3">
                <label class="block">Quantidade</label>
                <input type="number" id="quantity" value="2" class="w-full p-2 border rounded">
            </div>
            <div class="mb-3">
                <label class="block">Preço Unitário</label>
                <input type="number" id="unit_price" value="100" step="0.01" class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Criar Proposta</button>
        </form>
        
        <div id="resultado" class="mt-4 p-3 hidden rounded"></div>
    </div>

    <script>
        document.getElementById('formTeste').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const data = {
                client_id: parseInt(document.getElementById('client_id').value),
                validity_days: 30,
                lines: [{
                    article_id: parseInt(document.getElementById('article_id').value),
                    quantity: parseInt(document.getElementById('quantity').value),
                    unit_price: parseFloat(document.getElementById('unit_price').value)
                }],
                notes: 'Teste via formulário'
            };
            
            console.log('Enviando:', data);
            
            try {
                const response = await fetch('/api/proposals', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                console.log('Resposta:', result);
                
                const div = document.getElementById('resultado');
                div.classList.remove('hidden');
                
                if (result.success || result.proposal) {
                    div.className = 'mt-4 p-3 rounded bg-green-100 text-green-700';
                    div.innerHTML = '<strong>✅ Sucesso!</strong><br>' + JSON.stringify(result, null, 2);
                } else {
                    div.className = 'mt-4 p-3 rounded bg-red-100 text-red-700';
                    div.innerHTML = '<strong>❌ Erro!</strong><br>' + JSON.stringify(result, null, 2);
                }
            } catch (error) {
                console.error('Erro:', error);
                const div = document.getElementById('resultado');
                div.classList.remove('hidden');
                div.className = 'mt-4 p-3 rounded bg-red-100 text-red-700';
                div.innerHTML = '<strong>❌ Erro!</strong><br>' + error.message;
            }
        });
    </script>
</body>
</html>