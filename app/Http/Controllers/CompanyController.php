<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::getSettings();
        return view('company.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = CompanySetting::getSettings();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255'
        ]);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Configurações atualizadas com sucesso!'
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $settings = CompanySetting::getSettings();

        // Delete old logo if exists
        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $path = $request->file('logo')->store('company-logos', 'public');
        $settings->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'path' => Storage::url($path),
            'message' => 'Logotipo atualizado com sucesso!'
        ]);
    }

    public function deleteLogo()
    {
        $settings = CompanySetting::getSettings();

        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logotipo removido com sucesso!'
        ]);
    }
}