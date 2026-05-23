<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Email do Convidado</label>
        <input type="email" name="invite_email" class="w-full px-3 py-2 border rounded-md">
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Permissões</label>
        <select name="role" class="w-full px-3 py-2 border rounded-md">
            <option value="member">Membro</option>
            <option value="admin">Administrador</option>
        </select>
    </div>

    <button type="button" onclick="addUser()" class="text-indigo-600 text-sm hover:text-indigo-800">
        + Adicionar outro utilizador
    </button>
</div>