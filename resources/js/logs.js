import { createApp } from 'vue'
import LogsTable from './components/LogsTable.vue'

document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('logs-table-app');
    
    if (element) {
        let logsData = [];
        try {
            logsData = JSON.parse(element.dataset.logs || '[]');
        } catch (e) {
            console.error('Erro ao carregar logs:', e);
        }
        
        const app = createApp(LogsTable, {
            logsData: logsData
        });
        
        app.mount(element);
    }
});