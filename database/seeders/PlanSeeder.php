<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'name' => 'Básico',
            'slug' => 'basico',
            'description' => 'Ideal para pequenas empresas.',
            'price' => 29.90,
            'currency' => 'EUR',
            'billing_period' => 'monthly',
            'features' => [
                'dashboard' => true,
                'entities' => 50,
                'articles' => 50,
                'proposals' => 25,
                'orders' => 25,
                'reports' => false,
                'api_access' => false,
            ],
            'limits' => [
                'users' => 3,
                'entities' => 50,
                'articles' => 50,
                'proposals' => 25,
                'orders' => 25,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Plan::create([
            'name' => 'Profissional',
            'slug' => 'profissional',
            'description' => 'Para empresas em crescimento.',
            'price' => 59.90,
            'currency' => 'EUR',
            'billing_period' => 'monthly',
            'features' => [
                'dashboard' => true,
                'entities' => 200,
                'articles' => 200,
                'proposals' => 100,
                'orders' => 100,
                'reports' => true,
                'api_access' => true,
            ],
            'limits' => [
                'users' => 10,
                'entities' => 200,
                'articles' => 200,
                'proposals' => 100,
                'orders' => 100,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Plan::create([
            'name' => 'Empresarial',
            'slug' => 'empresarial',
            'description' => 'Para grandes empresas.',
            'price' => 129.90,
            'currency' => 'EUR',
            'billing_period' => 'monthly',
            'features' => [
                'dashboard' => true,
                'entities' => 1000,
                'articles' => 1000,
                'proposals' => 500,
                'orders' => 500,
                'reports' => true,
                'api_access' => true,
            ],
            'limits' => [
                'users' => 50,
                'entities' => 1000,
                'articles' => 1000,
                'proposals' => 500,
                'orders' => 500,
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}