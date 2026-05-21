<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Proposal;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    
    public function stats()
    {
        $totalClients = Entity::whereIn('type', ['client', 'both'])->count();
        
        $totalProposals = Proposal::count();
        
        $totalOrders = Order::count();
        
        $totalRevenue = Order::sum('total_value');
        
        return response()->json([
            'clients' => $totalClients,
            'proposals' => $totalProposals,
            'orders' => $totalOrders,
            'revenue' => $totalRevenue
        ]);
    }
}