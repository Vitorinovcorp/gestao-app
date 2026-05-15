<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestão')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            transition: all 0.3s;
        }

        .sidebar .nav-item:hover {
            background-color: #374151;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-4 text-xl font-bold border-b border-gray-700">
                Sistema Gestão
            </div>
            <nav class="flex-1 mt-4">
                <a href="{{ url('/dashboard') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Dashboard</a>
                <a href="{{ url('/entities') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Clientes/Fornecedores</a>
                <a href="{{ url('/contacts') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Contactos</a>
                <a href="{{ url('/articles') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Artigos</a>
                <a href="{{ url('/proposals') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Propostas</a>
                <a href="{{ url('/orders') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Encomendas</a>
                <a href="{{ url('/supplier-invoices') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Faturas Fornecedor</a>
                <a href="{{ url('/calendar') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Calendário</a>
                <a href="{{ url('/archive') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Arquivo Digital</a>
                <a href="{{ url('/users') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Utilizadores</a>
                <a href="{{ url('/permissions') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Permissões</a>
                <a href="{{ url('/logs') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Logs</a>
                <a href="{{ url('/settings') }}" class="nav-item block px-4 py-2 hover:bg-gray-700">Configurações</a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="text-sm text-gray-400 mb-2">{{ Auth::user()->name }}</div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-400 hover:text-red-300">Sair</button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <nav class="bg-white shadow-sm">
                <div class="px-6 py-3">
                    <h1 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>