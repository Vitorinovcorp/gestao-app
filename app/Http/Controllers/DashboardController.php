<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Proposal;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    
    public function stats()
    {
        // Contar clientes (entidades do tipo client ou both)
        $totalClients = Entity::whereIn('type', ['client', 'both'])->count();
        
        // Contar propostas
        $totalProposals = Proposal::count();
        
        // Contar encomendas
        $totalOrders = Order::count();
        
        // Somar valor total das encomendas
        $totalRevenue = Order::sum('total_value');
        
        return response()->json([
            'clients' => $totalClients,
            'proposals' => $totalProposals,
            'orders' => $totalOrders,
            'revenue' => $totalRevenue
        ]);
    }
}