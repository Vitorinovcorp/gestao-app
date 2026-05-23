<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Configuração de Permissões</label>
        <p class="text-sm text-gray-600 mb-3">
            Defina os níveis de acesso para os utilizadores do seu tenant.
        </p>
        
        <div class="space-y-2">
            <div class="flex items-center">
                <input type="checkbox" id="perm_manage_users" name="permissions[]" value="manage_users" 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="perm_manage_users" class="ml-2 text-sm text-gray-700">Gerir Utilizadores</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="perm_manage_entities" name="permissions[]" value="manage_entities" 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="perm_manage_entities" class="ml-2 text-sm text-gray-700">Gerir Entidades</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="perm_manage_proposals" name="permissions[]" value="manage_proposals" 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="perm_manage_proposals" class="ml-2 text-sm text-gray-700">Gerir Propostas</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="perm_manage_orders" name="permissions[]" value="manage_orders" 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="perm_manage_orders" class="ml-2 text-sm text-gray-700">Gerir Encomendas</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="perm_view_reports" name="permissions[]" value="view_reports" 
                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="perm_view_reports" class="ml-2 text-sm text-gray-700">Ver Relatórios</label>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Papel para novos utilizadores</label>
        <select name="default_role" class="w-full px-3 py-2 border rounded-md">
            <option value="member">Membro</option>
            <option value="admin">Administrador</option>
        </select>
    </div>
</div>