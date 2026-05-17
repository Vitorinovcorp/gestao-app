<script setup lang="ts">
import { ref, computed } from 'vue'
import DataTable from './DataTable.vue'

const props = defineProps<{
    logsData: any[]
}>()

const logs = ref(props.logsData || [])

const columns = [
    { accessorKey: 'data', header: 'Data' },
    { accessorKey: 'hora', header: 'Hora' },
    { accessorKey: 'utilizador', header: 'Utilizador' },
    { accessorKey: 'menu', header: 'Menu' },
    { accessorKey: 'acao', header: 'Acção' },
    { accessorKey: 'dispositivo', header: 'Dispositivo' },
    { accessorKey: 'ip', header: 'IP' },
]
</script>

<template>
    <div class="container mx-auto py-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Registo de Atividades</h2>
            <span class="text-sm text-gray-500">
                Total: {{ logs.length }} registos
            </span>
        </div>
        
        <DataTable :columns="columns" :data="logs" />
    </div>
</template>