<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenants = $this->tenantService->getUserTenants(auth()->user());
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tenant = $this->tenantService->createTenant(auth()->user(), $request->all());

        return redirect()->route('tenants.index')->with('success', 'Tenant criado com sucesso!');
    }

    public function switch(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $user = auth()->user();

        if ($this->tenantService->switchTenant($tenant, $user)) {
            return redirect()->back()->with('success', 'Tenant alterado para: ' . $tenant->name);
        }

        return redirect()->back()->with('error', 'Não tem permissão para aceder a este tenant');
    }

    public function settings()
    {
        $tenant = $this->tenantService->getActiveTenant();
        return view('tenants.settings', compact('tenant'));
    }

    public function updateSettings(Request $request)
    {
        $tenant = $this->tenantService->getActiveTenant();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tenant->update($request->only(['name', 'primary_color', 'settings']));

        return redirect()->back()->with('success', 'Configurações do tenant atualizadas!');
    }
}