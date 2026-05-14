@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-4">Bem-vindo, {{ Auth::user()->name }}!</h2>
    <p class="text-gray-600">Email: {{ Auth::user()->email }}</p>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-blue-100 p-4 rounded-lg">
            <h3 class="font-bold">Clientes</h3>
            <p class="text-2xl" id="totalClients">0</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg">
            <h3 class="font-bold">Propostas</h3>
            <p class="text-2xl" id="totalProposals">0</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg">
            <h3 class="font-bold">Encomendas</h3>
            <p class="text-2xl" id="totalOrders">0</p>
        </div>
        <div class="bg-purple-100 p-4 rounded-lg">
            <h3 class="font-bold">Faturação</h3>
            <p class="text-2xl" id="totalRevenue">0€</p>
        </div>
    </div>
</div>

<script>
    fetch('/api/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalClients').textContent = data.clients || 0;
            document.getElementById('totalProposals').textContent = data.proposals || 0;
            document.getElementById('totalOrders').textContent = data.orders || 0;
            document.getElementById('totalRevenue').textContent = (data.revenue || 0) + '€';
        });
</script>
@endsection