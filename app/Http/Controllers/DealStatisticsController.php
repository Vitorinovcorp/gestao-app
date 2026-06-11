<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? 35;

        $query = DB::table('deal_lines')
            ->join('deals', 'deal_lines.deal_id', '=', 'deals.id')
            ->join('articles', 'deal_lines.article_id', '=', 'articles.id')
            ->select(
                'articles.id',
                'articles.name as article_name',
                'articles.reference',
                DB::raw('SUM(deal_lines.quantity) as total_quantity'),
                DB::raw('SUM(deal_lines.total_price) as total_value')
            )
            ->where('deals.tenant_id', $tenantId);

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

        $users = User::where('tenant_id', $tenantId)->get();
        $stages = ['lead', 'proposal', 'negotiation', 'follow_up', 'won', 'lost'];

        return view('deals.statistics', compact('stats', 'users', 'stages'));
    }

    public function details($id)
    {
        $article = Article::with(['dealLines.deal.entity', 'dealLines.deal.owner'])
            ->findOrFail($id);

        return view('deals.statistics-details', compact('article'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id ?? 35;

        $query = DB::table('deal_lines')
            ->join('deals', 'deal_lines.deal_id', '=', 'deals.id')
            ->join('articles', 'deal_lines.article_id', '=', 'articles.id')
            ->select(
                'articles.reference',
                'articles.name as article_name',
                DB::raw('SUM(deal_lines.quantity) as total_quantity'),
                DB::raw('SUM(deal_lines.total_price) as total_value')
            )
            ->where('deals.tenant_id', $tenantId);

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
        fputcsv($handle, ['Referência', 'Produto', 'Quantidade Total', 'Valor Total (€)']);

        foreach ($stats as $stat) {
            fputcsv($handle, [
                $stat->reference,
                $stat->article_name,
                $stat->total_quantity,
                number_format($stat->total_value, 2)
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}