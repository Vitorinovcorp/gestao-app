<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function dashboard()
    {
        return view('cliente.dashboard', [
            'user' => Auth::user(),
            'totalPropostas' => 0,
            'totalEncomendas' => 0,
            'totalGasto' => 0,
            'ultimasPropostas' => []
        ]);
    }
    
    public function propostas()
    {
        return view('cliente.propostas', [
            'propostas' => []
        ]);
    }
    
    public function propostaDetalhe($id)
    {
        return view('cliente.proposta-detalhe', [
            'proposta' => null
        ]);
    }
    
    public function downloadProposta($id)
    {
        return redirect()->back()->with('error', 'Funcionalidade em desenvolvimento');
    }
    
    public function encomendas()
    {
        return view('cliente.encomendas', [
            'encomendas' => []
        ]);
    }
    
    public function encomendaDetalhe($id)
    {
        return view('cliente.encomenda-detalhe', [
            'encomenda' => null
        ]);
    }
    
    public function perfil()
    {
        return view('cliente.perfil', [
            'user' => Auth::user()
        ]);
    }
    
    public function atualizarPerfil(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($user ? $user->id : 'NULL'),
        ]);
        
        if ($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            }
        
        return redirect()->route('cliente.perfil')->with('success', 'Perfil atualizado!');
    }
}