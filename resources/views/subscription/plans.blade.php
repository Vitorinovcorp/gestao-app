@extends('layouts.app')

@section('title', 'Planos')
@section('header', 'Escolha o seu Plano')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="border rounded-lg p-6 hover:shadow-lg transition">
            <h3 class="text-xl font-bold text-gray-800">{{ $plan->name }}</h3>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($plan->price, 2) }} €</p>
            <p class="text-sm text-gray-500">{{ $plan->description }}</p>

            <ul class="mt-4 space-y-2">
                @foreach($plan->limits as $key => $limit)
                <li class="flex items-center text-sm text-gray-600">
                    <i class="fa-solid fa-check text-green-500 mr-2"></i>
                    {{ ucfirst($key) }}: {{ $limit }}
                </li>
                @endforeach
            </ul>

            @if(isset($currentPlan) && $plan->id === $currentPlan->id)
                <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed">
                    Plano Atual
                </button>
            @else
                <form action="{{ route('subscription.change', $plan) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        @if(isset($currentPlan) && $plan->price > $currentPlan->price)
                            <i class="fa-solid fa-arrow-up"></i> Upgrade 
                        @else
                            <i class="fa-solid fa-arrow-down"></i> Downgrade 
                        @endif
                    </button>
                </form>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection