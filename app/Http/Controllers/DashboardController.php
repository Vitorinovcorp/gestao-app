<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    
    public function stats()
    {
        return response()->json([
            'clients' => 0,
            'suppliers' => 0,
            'proposals' => 0,
            'orders' => 0,
            'revenue' => 0
        ]);
    }
}