<template>
  <div class="container mx-auto py-10 space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold">Gestão de Acessos - Utilizadores</h1>
      <Button @click="openCreateModal" :disabled="!canCreate">
        <Plus class="mr-2 h-4 w-4" />
        Novo Utilizador
      </Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Lista de Utilizadores</CardTitle>
        <CardDescription>
          Gerencie os utilizadores cadastrados no sistema
        </CardDescription>
      </CardHeader>
      <CardContent>
        <UsersTable 
          :users="users"
          :is-loading="isLoading"
          :can-edit="canEdit"
          :can-delete="canDelete"
          @edit="editUser"
          @delete="deleteUser"
          @toggle-status="toggleUserStatus"
        />
      </CardContent>
    </Card>

    <!-- Modal de Criar/Editar Usuário -->
    <Modal 
      :is-open="isModalOpen" 
      :title="editingUser ? 'Editar Utilizador' : 'Novo Utilizador'"
      @close="closeModal"
    >
      <template #content>
        <UserForm 
          ref="userFormRef"
          :editing-user="editingUser"
          :is-loading="isSubmitting"
          @submit="handleSubmit"
          @cancel-edit="closeModal"
        />
      </template>
      <template #footer>
        <div class="flex gap-2">
          <Button variant="outline" @click="closeModal" :disabled="isSubmitting">
            Cancelar
          </Button>
          <Button @click="submitForm" :disabled="isSubmitting">
            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            {{ editingUser ? 'Atualizar' : 'Criar' }}
          </Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Plus, Loader2 } from 'lucide-vue-next'
import UserForm from '@/components/UserForm.vue'
import UsersTable from '@/components/UsersTable.vue'
import Modal from '@/components/Modal.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import type { User, UserFormData } from '@/types/user'

const { toast } = useToast()
const users = ref<User[]>([])
const editingUser = ref<User | null>(null)
const isLoading = ref(false)
const isSubmitting = ref(false)
const isModalOpen = ref(false)
const userFormRef = ref<InstanceType<typeof UserForm> | null>(null)

// Verificar permissões do usuário logado
const currentUserPermissions = ref<string[]>([])

const canCreate = computed(() => currentUserPermissions.value.includes('create users'))
const canEdit = computed(() => currentUserPermissions.value.includes('edit users'))
const canDelete = computed(() => currentUserPermissions.value.includes('delete users'))

// Configuração do token CSRF
const getCsrfToken = () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  return token || ''
}

// Função para fazer requisições à API
const apiRequest = async (url: string, options: RequestInit = {}) => {
  const defaultOptions: RequestInit = {
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin'
  }

  const response = await fetch(url, { ...defaultOptions, ...options })
  
  if (!response.ok) {
    const error = await response.json()
    throw new Error(error.message || Object.values(error.errors || {}).flat().join(', ') || 'Erro na requisição')
  }
  
  return response.json()
}

// Carregar usuários
const loadUsers = async () => {
  isLoading.value = true
  try {
    const response = await apiRequest('/api/users')
    users.value = response.data.map((apiUser: any) => ({
      id: apiUser.id.toString(),
      nome: apiUser.name,
      email: apiUser.email,
      telefone: apiUser.telefone || '',
      grupoPermissoes: apiUser.grupo_permissoes || 'visualizador',
      estado: apiUser.status === 'active' ? 'ativo' : 'inativo'
    }))
  } catch (error: any) {
    console.error('Erro ao carregar usuários:', error)
    toast({
      title: 'Erro',
      description: error.message || 'Não foi possível carregar os usuários',
      variant: 'destructive'
    })
  } finally {
    isLoading.value = false
  }
}

// Criar usuário
const createUser = async (data: UserFormData) => {
  try {
    // Gerar senha temporária
    const tempPassword = Math.random().toString(36).slice(-8)
    
    const response = await apiRequest('/api/users', {
      method: 'POST',
      body: JSON.stringify({
        name: data.nome,
        email: data.email,
        telefone: data.telefone,
        grupo_permissoes: data.grupoPermissoes,
        status: data.estado === 'ativo' ? 'active' : 'inactive',
        password: tempPassword,
        password_confirmation: tempPassword
      })
    })

    // Adicionar à lista
    const newUser: User = {
      id: response.user.id.toString(),
      nome: data.nome,
      email: data.email,
      telefone: data.telefone,
      grupoPermissoes: data.grupoPermissoes,
      estado: data.estado
    }
    users.value.push(newUser)
    
    toast({
      title: 'Sucesso',
      description: `Utilizador ${data.nome} criado com sucesso!`,
      duration: 5000
    })
    
    // Se quiser mostrar a senha temporária
    toast({
      title: 'Senha Temporária',
      description: `Senha: ${tempPassword} - Guarde esta senha para enviar ao usuário`,
      duration: 10000
    })
    
    closeModal()
  } catch (error: any) {
    console.error('Erro ao criar usuário:', error)
    toast({
      title: 'Erro',
      description: error.message || 'Não foi possível criar o usuário',
      variant: 'destructive'
    })
    throw error
  }
}

// Atualizar usuário
const updateUser = async (data: UserFormData) => {
  if (!editingUser.value) return
  
  try {
    const updateData: any = {
      name: data.nome,
      email: data.email,
    }
    
    // Só incluir telefone e grupo_permissoes se existirem no controller
    if (data.telefone) updateData.telefone = data.telefone
    if (data.grupoPermissoes) updateData.grupo_permissoes = data.grupoPermissoes
    
    await apiRequest(`/api/users/${editingUser.value.id}`, {
      method: 'PUT',
      body: JSON.stringify(updateData)
    })

    // Atualizar na lista
    const index = users.value.findIndex(u => u.id === editingUser.value!.id)
    if (index !== -1) {
      users.value[index] = {
        ...editingUser.value,
        ...data
      }
    }
    
    toast({
      title: 'Sucesso',
      description: 'Utilizador atualizado com sucesso!'
    })
    
    closeModal()
  } catch (error: any) {
    console.error('Erro ao atualizar usuário:', error)
    toast({
      title: 'Erro',
      description: error.message || 'Não foi possível atualizar o usuário',
      variant: 'destructive'
    })
    throw error
  }
}

// Alternar status (usando is_active)
const toggleUserStatus = async (user: User) => {
  if (!canEdit.value) {
    toast({
      title: 'Sem permissão',
      description: 'Não tem permissão para editar utilizadores',
      variant: 'destructive'
    })
    return
  }
  
  isLoading.value = true
  try {
    const response = await apiRequest(`/api/users/${user.id}/toggle-status`, {
      method: 'POST'
    })

    // Atualizar status baseado na resposta
    const index = users.value.findIndex(u => u.id === user.id)
    if (index !== -1) {
      // O controller retorna is_active boolean
      const isActive = response.is_active
      users.value[index].estado = isActive ? 'ativo' : 'inativo'
    }
    
    toast({
      title: 'Sucesso',
      description: response.message || `Utilizador ${user.nome} ${user.estado === 'ativo' ? 'desativado' : 'ativado'} com sucesso!`
    })
  } catch (error: any) {
    console.error('Erro ao alternar status:', error)
    toast({
      title: 'Erro',
      description: error.message || 'Não foi possível alterar o status',
      variant: 'destructive'
    })
  } finally {
    isLoading.value = false
  }
}

// Excluir usuário
const deleteUser = async (id: string) => {
  if (!canDelete.value) {
    toast({
      title: 'Sem permissão',
      description: 'Não tem permissão para eliminar utilizadores',
      variant: 'destructive'
    })
    return
  }
  
  const userToDelete = users.value.find(u => u.id === id)
  if (!userToDelete) return
  
  if (!confirm(`Tem certeza que deseja excluir o utilizador ${userToDelete.nome}?`)) return
  
  isLoading.value = true
  try {
    await apiRequest(`/api/users/${id}`, {
      method: 'DELETE'
    })

    users.value = users.value.filter(u => u.id !== id)
    
    toast({
      title: 'Sucesso',
      description: 'Utilizador removido com sucesso!'
    })
  } catch (error: any) {
    console.error('Erro ao excluir usuário:', error)
    toast({
      title: 'Erro',
      description: error.message || 'Não foi possível excluir o usuário',
      variant: 'destructive'
    })
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  if (!canCreate.value) {
    toast({
      title: 'Sem permissão',
      description: 'Não tem permissão para criar utilizadores',
      variant: 'destructive'
    })
    return
  }
  editingUser.value = null
  isModalOpen.value = true
}

const editUser = (user: User) => {
  if (!canEdit.value) {
    toast({
      title: 'Sem permissão',
      description: 'Não tem permissão para editar utilizadores',
      variant: 'destructive'
    })
    return
  }
  editingUser.value = user
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingUser.value = null
  if (userFormRef.value) {
    // Reset form
  }
}

const submitForm = () => {
  if (userFormRef.value) {
    userFormRef.value.submitForm()
  }
}

const handleSubmit = async (data: UserFormData) => {
  isSubmitting.value = true
  try {
    if (editingUser.value) {
      await updateUser(data)
    } else {
      await createUser(data)
    }
  } catch (error) {
    // Error already handled in individual functions
  } finally {
    isSubmitting.value = false
  }
}

// Carregar permissões do usuário atual
const loadUserPermissions = async () => {
  try {
    const response = await apiRequest('/user/permissions')
    currentUserPermissions.value = response.permissions || []
  } catch (error) {
    console.error('Erro ao carregar permissões:', error)
  }
}

onMounted(() => {
  loadUserPermissions()
  loadUsers()
})
</script>