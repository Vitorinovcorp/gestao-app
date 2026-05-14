<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ContactRole;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view settings')->only([
            'countries', 'contactRoles', 'companySettings', 'generalSettings'
        ]);
        $this->middleware('permission:create settings')->only([
            'storeCountry', 'storeContactRole'
        ]);
        $this->middleware('permission:edit settings')->only([
            'updateCountry', 'updateContactRole', 'updateCompany', 'updateGeneral'
        ]);
        $this->middleware('permission:delete settings')->only([
            'deleteCountry', 'deleteContactRole', 'deleteLogo'
        ]);
    }
    
    // ==================== PAÍSES ====================
    public function countries()
    {
        $countries = Country::orderBy('name')->get();
        return response()->json($countries);
    }
    
    public function storeCountry(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:2|unique:countries,code',
            'name' => 'required|string|max:100',
            'phone_code' => 'nullable|string|max:5'
        ]);
        
        $country = Country::create($validated);
        
        return response()->json([
            'message' => 'País criado com sucesso',
            'country' => $country
        ], 201);
    }
    
    public function updateCountry(Request $request, Country $country)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|size:2|unique:countries,code,' . $country->id,
            'name' => 'sometimes|string|max:100',
            'phone_code' => 'nullable|string|max:5'
        ]);
        
        $country->update($validated);
        
        return response()->json([
            'message' => 'País atualizado com sucesso',
            'country' => $country
        ]);
    }
    
    public function deleteCountry(Country $country)
    {
        $country->delete();
        return response()->json(['message' => 'País eliminado com sucesso']);
    }
    
    // ==================== FUNÇÕES DE CONTACTO ====================
    public function contactRoles()
    {
        $roles = ContactRole::orderBy('name')->get();
        return response()->json($roles);
    }
    
    public function storeContactRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:contact_roles,name',
            'slug' => 'required|string|max:100|unique:contact_roles,slug',
            'description' => 'nullable|string'
        ]);
        
        $role = ContactRole::create($validated);
        
        return response()->json([
            'message' => 'Função criada com sucesso',
            'role' => $role
        ], 201);
    }
    
    public function updateContactRole(Request $request, ContactRole $role)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:contact_roles,name,' . $role->id,
            'slug' => 'sometimes|string|max:100|unique:contact_roles,slug,' . $role->id,
            'description' => 'nullable|string'
        ]);
        
        $role->update($validated);
        
        return response()->json([
            'message' => 'Função atualizada com sucesso',
            'role' => $role
        ]);
    }
    
    public function deleteContactRole(ContactRole $role)
    {
        $role->delete();
        return response()->json(['message' => 'Função eliminada com sucesso']);
    }
    
    // ==================== CONFIGURAÇÕES DA EMPRESA ====================
    public function companySettings()
    {
        $settings = CompanySetting::first();
        return response()->json($settings);
    }
    
    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url'
        ]);
        
        $settings = CompanySetting::first();
        
        if (!$settings) {
            $settings = CompanySetting::create($validated);
        } else {
            $settings->update($validated);
        }
        
        return response()->json([
            'message' => 'Configurações atualizadas com sucesso',
            'settings' => $settings
        ]);
    }
    
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        $settings = CompanySetting::first();
        
        if ($settings && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
        }
        
        $path = $request->file('logo')->store('logos', 'public');
        
        if (!$settings) {
            $settings = CompanySetting::create(['logo_path' => $path]);
        } else {
            $settings->update(['logo_path' => $path]);
        }
        
        return response()->json([
            'message' => 'Logo enviado com sucesso',
            'path' => asset('storage/' . $path)
        ]);
    }
    
    public function deleteLogo()
    {
        $settings = CompanySetting::first();
        
        if ($settings && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }
        
        return response()->json(['message' => 'Logo removido com sucesso']);
    }
    
    // ==================== CONFIGURAÇÕES GERAIS ====================
    public function generalSettings()
    {
        return response()->json([
            'app_name' => config('app.name'),
            'app_debug' => config('app.debug'),
            'timezone' => config('app.timezone')
        ]);
    }
    
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_debug' => 'boolean',
            'timezone' => 'required|string'
        ]);
        
        // Atualizar .env
        $this->updateEnvFile($validated);
        
        return response()->json(['message' => 'Configurações atualizadas com sucesso']);
    }
    
    public function syncSettings()
    {
        \Artisan::call('config:cache');
        return response()->json(['message' => 'Configurações sincronizadas']);
    }
    
    private function updateEnvFile($settings)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        foreach ($settings as $key => $value) {
            $envKey = strtoupper($key);
            $envContent = preg_replace("/{$envKey}=.*/", "{$envKey}={$value}", $envContent);
        }
        
        file_put_contents($envFile, $envContent);
    }
}