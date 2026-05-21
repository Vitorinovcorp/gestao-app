<script setup lang="ts">
import { ref } from 'vue'
import DataTable from './DataTable.vue'

const props = defineProps<{
    logsData: any[]
}>()

const logs = ref(props.logsData || [])
const isClearing = ref(false)
const showConfirmModal = ref(false)

const columns = [
    { accessorKey: 'data', header: 'Data' },
    { accessorKey: 'hora', header: 'Hora' },
    { accessorKey: 'utilizador', header: 'Utilizador' },
    { accessorKey: 'menu', header: 'Menu' },
    { accessorKey: 'acao', header: 'Acção' },
    { accessorKey: 'dispositivo', header: 'Dispositivo' },
    { accessorKey: 'ip', header: 'IP' },
]

const clearOldLogs = async () => {
    isClearing.value = true
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        
        console.log('A enviar requisição para limpar logs...')
        console.log('CSRF Token:', csrfToken)
        
        const response = await fetch('/logs/clear-old', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ days: 90 })
        })
        
        console.log('Resposta status:', response.status)
        
        if (!response.ok) {
            const errorText = await response.text()
            console.error('Erro HTTP:', response.status, errorText)
            throw new Error(`Erro ${response.status}: ${errorText}`)
        }
        
        const result = await response.json()
        console.log('Resultado:', result)
        
        if (result.success) {
            alert(result.message)
            window.location.reload()
        } else {
            alert('Erro ao limpar logs: ' + (result.message || 'Erro desconhecido'))
        }
    } catch (error) {
        console.error('Erro detalhado:', error)
        alert('Erro ao comunicar com o servidor. Verifique o console para mais detalhes.')
    } finally {
        isClearing.value = false
        showConfirmModal.value = false
    }
}
</script>

<template>
    <div class="container mx-auto py-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Registo de Atividades</h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">
                    Total: {{ logs.length }} registos
                </span>
                <button 
                    @click="showConfirmModal = true"
                    class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition disabled:opacity-50"
                    :disabled="isClearing"
                >
                    {{ isClearing ? 'A limpar...' : 'Limpar Logs Antigos' }}
                </button>
            </div>
        </div>
        
        <DataTable :columns="columns" :data="logs" />
        
        <!-- Modal de Confirmação -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">Confirmar Limpeza</h3>
                <p class="mb-4">Tem certeza que deseja remover todos os logs com mais de 90 dias?</p>
                <div class="flex justify-end gap-3">
                    <button 
                        @click="showConfirmModal = false"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800"
                        :disabled="isClearing"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="clearOldLogs"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50"
                        :disabled="isClearing"
                    >
                        {{ isClearing ? 'Removendo...' : 'Confirmar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>