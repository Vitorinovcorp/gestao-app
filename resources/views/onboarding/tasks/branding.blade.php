<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Empresa</label>
        <input type="text" name="company_name" value="{{ $tenant->name }}" 
               class="w-full px-3 py-2 border rounded-md">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Logotipo</label>
        <input type="file" name="logo" class="w-full px-3 py-2 border rounded-md">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cor Principal</label>
        <input type="color" name="primary_color" value="{{ $tenant->primary_color }}" 
               class="w-12 h-12 border rounded-md cursor-pointer">
    </div>
</div>