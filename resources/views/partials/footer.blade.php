<!-- Footer -->
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-left">
                <p class="text-sm text-gray-500">
                    © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
                </p>
            </div>
            
            <div class="flex gap-6">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition">
                    Dashboard
                </a>
                <a href="{{ route('chat.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition">
                    Chat IA
                </a>
                <a href="{{ route('ai-suggestions.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition">
                    Sugestões IA
                </a>
            </div>
            
            <div class="text-center md:text-right">
                <p class="text-xs text-gray-400">
                    Versão 1.0.0
                </p>
            </div>
        </div>
    </div>
</footer>