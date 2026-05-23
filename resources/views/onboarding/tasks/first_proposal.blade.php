<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
        <select name="proposal_client" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            <option value="">Selecione um cliente</option>
            @if(isset($clients) && count($clients) > 0)
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            @else
                <option value="" disabled>Nenhum cliente encontrado. Crie um cliente no passo anterior.</option>
            @endif
        </select>
        @if(!isset($clients) || count($clients) == 0)
            <p class="text-xs text-red-500 mt-1">⚠️ Não foi encontrado nenhum cliente. Volte ao passo anterior e crie um cliente.</p>
        @endif
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Título da Proposta</label>
        <input type="text" name="proposal_title" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="Proposta Comercial">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
        <input type="number" step="0.01" name="proposal_amount" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="1000.00">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <textarea name="proposal_description" rows="3" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">Proposta comercial para o primeiro cliente.</textarea>
    </div>
</div>