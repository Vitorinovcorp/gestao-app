import { createColumnHelper } from '@tanstack/vue-table'
import type { Log } from '@/types/logs'

const columnHelper = createColumnHelper<Log>()

export const columns = [
    columnHelper.accessor('data', {
        header: 'Data',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('hora', {
        header: 'Hora',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('utilizador', {
        header: 'Utilizador',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('menu', {
        header: 'Menu',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('acao', {
        header: 'Acção',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('dispositivo', {
        header: 'Dispositivo',
        cell: ({ getValue }) => getValue(),
    }),
    columnHelper.accessor('ip', {
        header: 'IP',
        cell: ({ getValue }) => getValue(),
    }),
]