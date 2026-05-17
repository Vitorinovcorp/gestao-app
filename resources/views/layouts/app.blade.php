<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestão')</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .sidebar {
            background: linear-gradient(180deg, #6D5BD0 0%, #6B56C5 100%);
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .nav-item {
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.18);
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="sidebar w-72 text-white flex flex-col">

            <!-- Logo - REDUZIDO -->
            <div class="px-6 py-5">
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-chart-line"></i>
                    Gestão App
                </h1>
            </div>

            <!-- Menu - ESPAÇAMENTO REDUZIDO -->
            <nav class="flex-1 px-3 space-y-1">

                <a href="{{ url('/dashboard') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-gauge w-5"></i>
                    Dashboard
                </a>

                <a href="{{ url('/entities') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-building w-5"></i>
                    Entidades
                </a>

                <a href="{{ url('/contacts') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-address-book w-5"></i>
                    Contactos
                </a>

                <a href="{{ url('/articles') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-box w-5"></i>
                    Artigos
                </a>

                <a href="{{ url('/proposals') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-file-lines w-5"></i>
                    Propostas
                </a>

                <a href="{{ url('/orders') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-cart-shopping w-5"></i>
                    Encomendas
                </a>

                <a href="{{ url('/calendar') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-calendar w-5"></i>
                    Calendário
                </a>

                <a href="{{ url('/logs') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i>
                    Logs
                </a>

                <a href="{{ url('/users') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-users w-5"></i>
                    Utilizadores
                </a>

                <a href="{{ url('/settings') }}"
                    class="nav-item flex items-center gap-3 px-4 py-2 rounded-xl text-base">
                    <i class="fa-solid fa-gear w-5"></i>
                    Configurações
                </a>

            </nav>

            <!-- Footer - REDUZIDO -->
            <div class="px-3 py-4 border-t border-white/20">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="nav-item w-full flex items-center gap-3 px-4 py-2 rounded-xl text-base text-left">
                        <i class="fa-solid fa-right-from-bracket w-5"></i>
                        Sair
                    </button>
                </form>

            </div>

        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Navbar -->
            <nav class="bg-white shadow-sm">
                <div class="px-6 py-3">
                    <h1 class="text-xl font-semibold text-gray-800">
                        @yield('header', 'Dashboard')
                    </h1>
                </div>
            </nav>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>

        </div>

    </div>
</body>

</html>