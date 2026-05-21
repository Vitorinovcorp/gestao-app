@php
    use App\Services\TenantService;
    $tenantService = app(TenantService::class);
    $currentTenant = $tenantService->getActiveTenant();
    $userTenants = auth()->check() ? $tenantService->getUserTenants(auth()->user()) : collect();
@endphp

<div class="relative" x-data="{ open: false }">
    <!-- Botão do tenant atual -->
    <button 
        @click="open = !open"
        class="tenant-switcher-btn flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition"
    >
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" 
             style="background-color: {{ $currentTenant?->primary_color ?? '#6D5BD0' }}">
            {{ substr($currentTenant?->name ?? 'S', 0, 1) }}
        </div>
        <div class="hidden md:block text-left">
            <div class="text-xs text-gray-500">Tenant Ativo</div>
            <div class="text-sm font-semibold text-gray-800 truncate max-w-[120px]">
                {{ $currentTenant?->name ?? 'Sem Tenant' }}
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
    </button>

    <!-- Dropdown de tenants -->
    <div 
        x-show="open" 
        @click.away="open = false"
        class="tenant-dropdown absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-50 border border-gray-100 overflow-hidden"
        style="display: none;"
    >
        <div class="p-2">
            <div class="text-xs text-gray-500 px-2 py-1">Seus Tenants</div>
            
            @foreach($userTenants as $tenant)
                <!-- FORMA CORRETA: Formulário POST -->
                <form method="POST" action="{{ route('tenants.switch', $tenant->id) }}" class="block">
                    @csrf
                    <button type="submit" 
                            class="tenant-option w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition {{ $currentTenant?->id === $tenant->id ? 'bg-indigo-50' : '' }}"
                    >
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white"
                             style="background-color: {{ $tenant->primary_color ?? '#6D5BD0' }}">
                            {{ substr($tenant->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800">{{ $tenant->name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $tenant->isOwner(auth()->user()) ? 'Proprietário' : 'Membro' }}
                            </div>
                        </div>
                        @if($currentTenant?->id === $tenant->id)
                            <i class="fa-solid fa-check text-indigo-600"></i>
                        @endif
                    </button>
                </form>
            @endforeach

            <div class="border-t border-gray-100 my-1"></div>
            
            <a 
                href="{{ route('tenants.create') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-indigo-600"
            >
                <i class="fa-solid fa-plus"></i>
                <span class="text-sm">Criar Novo Tenant</span>
            </a>
            
            <a 
                href="{{ route('tenants.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-gray-600"
            >
                <i class="fa-solid fa-list"></i>
                <span class="text-sm">Gerenciar Tenants</span>
            </a>
        </div>
    </div>
</div>