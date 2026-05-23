<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Artigo *</label>
        <input type="text" name="article_name" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Referência</label>
        <input type="text" name="article_reference" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Preço</label>
        <input type="number" step="0.01" name="article_price" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">IVA</label>
        <select name="article_vat" class="w-full px-3 py-2 border rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <option value="23">23%</option>
            <option value="13">13%</option>
            <option value="6">6%</option>
            <option value="0">Isento</option>
        </select>
    </div>
</div>