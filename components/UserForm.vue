<!-- resources/js/components/UserForm.vue -->
<template>
  <form @submit="onSubmit" class="space-y-4">
    <!-- Campos do formulário (mesmo código anterior) -->
    <FormField v-slot="{ componentField }" name="nome">
      <FormItem>
        <FormLabel>Nome *</FormLabel>
        <FormControl>
          <Input 
            type="text" 
            placeholder="Digite o nome completo" 
            v-bind="componentField"
            :disabled="isLoading"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="email">
      <FormItem>
        <FormLabel>Email *</FormLabel>
        <FormControl>
          <Input 
            type="email" 
            placeholder="usuario@exemplo.com" 
            v-bind="componentField"
            :disabled="isLoading"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="telefone">
      <FormItem>
        <FormLabel>Telemóvel</FormLabel>
        <FormControl>
          <Input 
            type="tel" 
            placeholder="912345678" 
            v-bind="componentField"
            :disabled="isLoading"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="grupoPermissoes">
      <FormItem>
        <FormLabel>Grupo de Permissões *</FormLabel>
        <Select v-bind="componentField" :disabled="isLoading">
          <FormControl>
            <SelectTrigger>
              <SelectValue placeholder="Selecione um grupo" />
            </SelectTrigger>
          </FormControl>
          <SelectContent>
            <SelectItem v-for="grupo in gruposPermissoes" :key="grupo.value" :value="grupo.value">
              {{ grupo.label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="estado">
      <FormItem>
        <FormLabel>Estado *</FormLabel>
        <Select v-bind="componentField" :disabled="isLoading">
          <FormControl>
            <SelectTrigger>
              <SelectValue placeholder="Selecione o estado" />
            </SelectTrigger>
          </FormControl>
          <SelectContent>
            <SelectItem value="ativo">Ativo</SelectItem>
            <SelectItem value="inativo">Inativo</SelectItem>
          </SelectContent>
        </Select>
        <FormMessage />
      </FormItem>
    </FormField>
  </form>
</template>

<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { userSchema } from '@/schemas/userSchema'
import { gruposPermissoes } from '@/types/user'
import type { User } from '@/types/user'

const props = defineProps<{
  editingUser?: User | null
  isLoading?: boolean
}>()

const emit = defineEmits<{
  submit: [data: any]
  'cancel-edit': []
}>()

const form = useForm({
  validationSchema: toTypedSchema(userSchema),
  initialValues: props.editingUser || {
    nome: '',
    email: '',
    telefone: '',
    grupoPermissoes: '',
    estado: 'ativo'
  }
})

const onSubmit = form.handleSubmit((values) => {
  emit('submit', values)
  if (!props.editingUser && !props.isLoading) {
    form.resetForm()
  }
})

// Expor método para submit externo
const submitForm = () => {
  form.submitForm()
}

defineExpose({
  submitForm
})
</script>