<template>
  <Dialog v-model:open="open">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle>{{ isEditing ? 'Editar' : 'Novo' }} Cliente/Fornecedor</DialogTitle>
      </DialogHeader>
      
      <form @submit.prevent="handleSubmit">
        <div class="grid gap-4 py-4">
          <FormField v-slot="{ componentField }" name="type">
            <FormItem>
              <FormLabel>Tipo</FormLabel>
              <FormControl>
                <RadioGroup v-bind="componentField" class="flex gap-4">
                  <RadioGroupItem value="client">Cliente</RadioGroupItem>
                  <RadioGroupItem value="supplier">Fornecedor</RadioGroupItem>
                  <RadioGroupItem value="both">Ambos</RadioGroupItem>
                </RadioGroup>
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>
          
          <FormField v-slot="{ componentField }" name="nif">
            <FormItem>
              <FormLabel>NIF</FormLabel>
              <div class="flex gap-2">
                <FormControl>
                  <Input v-bind="componentField" placeholder="Número de Contribuinte" />
                </FormControl>
                <Button type="button" variant="outline" @click="fetchVIESData">
                  <Search class="h-4 w-4" />
                  Validar VIES
                </Button>
              </div>
              <FormMessage />
            </FormItem>
          </FormField>
          
          <FormField v-slot="{ componentField }" name="name">
            <FormItem>
              <FormLabel>Nome</FormLabel>
              <FormControl>
                <Input v-bind="componentField" />
              </FormControl>
              <FormMessage />
            </FormItem>
          </FormField>
          
          <!-- Campos adicionais... -->
        </div>
        
        <DialogFooter>
          <Button type="button" variant="outline" @click="open = false">
            Cancelar
          </Button>
          <Button type="submit">
            {{ isEditing ? 'Atualizar' : 'Criar' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'
import axios from 'axios'

const schema = toTypedSchema(
  yup.object({
    type: yup.string().required(),
    nif: yup.string().required('NIF é obrigatório').min(9, 'NIF inválido'),
    name: yup.string().required('Nome é obrigatório'),
    email: yup.string().email('Email inválido'),
    postal_code: yup.string().matches(/^\d{4}-\d{3}$/, 'Formato: XXXX-XXX')
  })
)

const { handleSubmit, setFieldValue } = useForm({ validationSchema: schema })

const fetchVIESData = async () => {
  const nif = await getFieldValue('nif')
  if (!nif) return
  
  const response = await axios.post('/api/vies/validate', { nif })
  if (response.data.valid) {
    setFieldValue('name', response.data.name)
    setFieldValue('address', response.data.address)
  }
}
</script>