<template>
  <div class="rounded-md border">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead v-for="column in columns" :key="column.key">
            {{ column.label }}
          </TableHead>
          <TableHead class="text-right">Ações</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="user in users" :key="user.id">
          <TableCell>{{ user.nome }}</TableCell>
          <TableCell>{{ user.email }}</TableCell>
          <TableCell>{{ user.telefone || '-' }}</TableCell>
          <TableCell>{{ getGrupoLabel(user.grupoPermissoes) }}</TableCell>
          <TableCell>
            <Badge :variant="user.estado === 'ativo' ? 'default' : 'secondary'">
              <span class="flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full" :class="user.estado === 'ativo' ? 'bg-green-500' : 'bg-gray-500'"></span>
                {{ user.estado === 'ativo' ? 'Ativo' : 'Inativo' }}
              </span>
            </Badge>
          </TableCell>
          <TableCell class="text-right">
            <div class="flex justify-end gap-2">
              <Button 
                v-if="canEdit" 
                variant="ghost" 
                size="sm" 
                @click="$emit('edit', user)"
                title="Editar"
              >
                <Pencil class="h-4 w-4" />
              </Button>
              <Button 
                v-if="canEdit" 
                variant="ghost" 
                size="sm" 
                @click="$emit('toggle-status', user)"
                :title="user.estado === 'ativo' ? 'Desativar' : 'Ativar'"
              >
                <Power class="h-4 w-4" :class="user.estado === 'ativo' ? 'text-green-600' : 'text-gray-400'" />
              </Button>
              <Button 
                v-if="canDelete" 
                variant="ghost" 
                size="sm" 
                @click="$emit('delete', user.id)"
                title="Excluir"
              >
                <Trash2 class="h-4 w-4 text-red-600" />
              </Button>
            </div>
          </TableCell>
        </TableRow>
        <TableRow v-if="users.length === 0 && !isLoading">
          <TableCell :colspan="columns.length + 1" class="text-center py-8">
            <div class="flex flex-col items-center gap-2">
              <Users class="h-12 w-12 text-gray-400" />
              <p class="text-gray-500">Nenhum utilizador encontrado</p>
              <Button v-if="canCreate" variant="outline" size="sm" @click="$emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                Criar primeiro utilizador
              </Button>
            </div>
          </TableCell>
        </TableRow>
        <TableRow v-if="isLoading">
          <TableCell :colspan="columns.length + 1" class="text-center py-8">
            <Loader2 class="h-6 w-6 animate-spin mx-auto" />
            <p class="mt-2 text-gray-500">Carregando utilizadores...</p>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Pencil, Trash2, Power, Loader2, Users, Plus } from 'lucide-vue-next'
import type { User } from '@/types/user'
import { gruposPermissoes } from '@/types/user'

const columns = [
  { key: 'nome', label: 'Nome' },
  { key: 'email', label: 'Email' },
  { key: 'telefone', label: 'Telemóvel' },
  { key: 'grupoPermissoes', label: 'Grupo de Permissões' },
  { key: 'estado', label: 'Estado' }
]

defineProps<{
  users: User[]
  isLoading?: boolean
  canEdit?: boolean
  canDelete?: boolean
  canCreate?: boolean
}>()

const emit = defineEmits<{
  edit: [user: User]
  delete: [id: string]
  'toggle-status': [user: User]
  create: []
}>()

const getGrupoLabel = (value: string) => {
  return gruposPermissoes.find(g => g.value === value)?.label || value
}
</script>