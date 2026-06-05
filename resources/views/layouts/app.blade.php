<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestão')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/inovcorp-logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/inovcorp-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/inovcorp-logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            overflow: hidden;
            height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #6D5BD0 0%, #6B56C5 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-nav::-webkit-scrollbar {
            display: none;
        }

        .nav-item {
            transition: all 0.3s ease;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.75rem !important;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.18);
        }

        .nav-item i {
            font-size: 1rem;
            width: 1.25rem;
        }

        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .logo-area {
            padding: 1rem 1.25rem !important;
        }

        .logo-area h1 {
            font-size: 1.25rem !important;
        }

        .sidebar-footer {
            padding: 0.75rem 0.75rem !important;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .menu-space {
            margin-top: 0.25rem !important;
        }

        .logo-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        /* Estilos para o Tenant Switcher */
        .tenant-switcher-btn {
            background: rgba(109, 91, 208, 0.1);
            border: 1px solid rgba(109, 91, 208, 0.2);
            transition: all 0.3s ease;
        }

        .tenant-switcher-btn:hover {
            background: rgba(109, 91, 208, 0.2);
        }

        .tenant-dropdown {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .tenant-option {
            transition: all 0.2s ease;
        }

        .tenant-option:hover {
            background: #f3f4f6;
        }

        .tenant-option.active {
            background: rgba(109, 91, 208, 0.1);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar text-white">
        <!-- Logo -->
        <div class="logo-area flex-shrink-0">
            <div class="font-bold flex items-center gap-2">
                <img src="{{ asset('images/inovcorp-logo.png') }}" alt="Logo" class="logo-img">
                <h1>Gestão App</h1>
            </div>
        </div>

        <!-- Menu -->
        <div class="sidebar-nav px-3">
            <div class="space-y-1 menu-space">
                <a href="{{ url('/dashboard') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-gauge"></i>
                    Dashboard
                </a>

                <a href="{{ url('/entities') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-building"></i>
                    Entidades
                </a>

                <a href="{{ url('/contacts') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-address-book"></i>
                    Contactos
                </a>

                <a href="{{ url('/articles') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-box"></i>
                    Artigos
                </a>

                <a href="{{ route('deals.statistics') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-chart-bar"></i>
                    Estatísticas de Produtos
                </a>

                <a href="{{ url('/proposals') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-file-lines"></i>
                    Propostas
                </a>

                <a href="{{ url('/orders') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Encomendas
                </a>

                <a href="{{ route('supplier-invoices.index') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Faturas Fornecedores
                </a>

                <a href="{{ url('/calendar') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-calendar"></i>
                    Calendário
                </a>

                <a href="{{ route('automation.index') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-robot"></i>
                    Automatização
                </a>

                <a href="{{ url('/logs') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Logs
                </a>

                <a href="{{ url('/users') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-users"></i>
                    Utilizadores
                </a>

                <a href="{{ route('public-forms.index') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Formulários Públicos
                </a>

                <a href="{{ route('permissions.index') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-lock"></i>
                    Permissões
                </a>

                <a href="{{ route('company.index') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-building"></i>
                    Empresa
                </a>

                <a href="{{ route('subscription.index') }}"
                    class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-credit-card"></i>
                    Subscrição
                </a>

                <a href="{{ route('deals.kanban') }}"
                    class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-tasks"></i>
                    Kanban
                </a>

                <a href="{{ url('/settings') }}" class="nav-item flex items-center gap-3">
                    <i class="fa-solid fa-gear"></i>
                    Configurações
                </a>
            </div>
        </div>

        <div class="sidebar-footer flex-shrink-0">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full flex items-center gap-3">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Sair
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <nav class="bg-white shadow-sm flex-shrink-0">
            <div class="px-6 py-3 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">
                    @yield('header', 'Dashboard')
                </h1>

                <div class="flex items-center gap-3">
                    <!-- Tenant Switcher -->
                    @auth
                    @include('layouts.tenant-switcher')
                    @endauth

                    <div class="relative group">
                        <button class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                            <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-sm">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="hidden md:block text-sm">{{ auth()->user()->name ?? 'Utilizador' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>