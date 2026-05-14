<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Área do Cliente')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-blue-600">Minha Conta</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ Auth::user()->name ?? 'Cliente' }}</span>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Menu -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="{{ route('cliente.dashboard') }}" class="py-3 px-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                    Dashboard
                </a>
                <a href="{{ route('cliente.propostas') }}" class="py-3 px-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                    Minhas Propostas
                </a>
                <a href="{{ route('cliente.encomendas') }}" class="py-3 px-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                    Minhas Encomendas
                </a>
                <a href="{{ route('cliente.perfil') }}" class="py-3 px-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                    Meu Perfil
                </a>
            </div>
        </div>
    </div>

    <!-- Conteúdo -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>