<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('deal_products')
            ->join('deals', 'deal_products.deal_id', '=', 'deals.id')
            ->join('articles', 'deal_products.article_id', '=', 'articles.id')
            ->select(
                'articles.id',
                'articles.name as article_name',
                'articles.reference',
                DB::raw('SUM(deal_products.quantity) as total_quantity'),
                DB::raw('SUM(deal_products.quantity * deal_products.price) as total_value')
            )
            ->where('deals.tenant_id', tenant()->id);

        // Filtros
        if ($request->filled('stage')) {
            $query->where('deals.stage', $request->stage);
        }

        if ($request->filled('owner_id')) {
            $query->where('deals.owner_id', $request->owner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('deals.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('deals.created_at', '<=', $request->date_to);
        }

        $stats = $query->groupBy('articles.id', 'articles.name', 'articles.reference')
            ->orderByDesc('total_value')
            ->paginate($request->get('per_page', 15));

        return view('deals.statistics', compact('stats'));
    }

    public function details($id)
    {
        $article = Article::with(['dealProducts.deal.entity', 'dealProducts.deal.owner'])
            ->findOrFail($id);

        return view('deals.article-details', compact('article'));
    }

    public function export(Request $request)
    {
        $query = DB::table('deal_products')
            ->join('deals', 'deal_products.deal_id', '=', 'deals.id')
            ->join('articles', 'deal_products.article_id', '=', 'articles.id')
            ->select(
                'articles.reference',
                'articles.name as article_name',
                DB::raw('SUM(deal_products.quantity) as total_quantity'),
                DB::raw('SUM(deal_products.quantity * deal_products.price) as total_value')
            )
            ->where('deals.tenant_id', tenant()->id);

        if ($request->filled('stage')) {
            $query->where('deals.stage', $request->stage);
        }

        if ($request->filled('owner_id')) {
            $query->where('deals.owner_id', $request->owner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('deals.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('deals.created_at', '<=', $request->date_to);
        }

        $stats = $query->groupBy('articles.reference', 'articles.name')
            ->orderByDesc('total_value')
            ->get();

        $filename = 'estatisticas_produtos_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, ['Referência', 'Produto', 'Quantidade Total', 'Valor Total']);

        foreach ($stats as $stat) {
            fputcsv($handle, [
                $stat->reference,
                $stat->article_name,
                $stat->total_quantity,
                $stat->total_value
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}