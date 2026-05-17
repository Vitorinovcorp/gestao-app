<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer');
        
        // Filtros
        if ($request->filled('menu')) {
            $query->where('log_name', $request->menu);
        }
        
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->latest()->paginate(50);
        
        // Transformar os dados
        $logs->getCollection()->transform(function ($activity) {
            $properties = [];
            if ($activity->properties) {
                $properties = is_string($activity->properties) 
                    ? json_decode($activity->properties, true) 
                    : (array) $activity->properties;
            }
            
            return [
                'id' => $activity->id,
                'data' => $activity->created_at->format('d/m/Y'),
                'hora' => $activity->created_at->format('H:i:s'),
                'utilizador' => $activity->causer?->name ?? 'Sistema',
                'menu' => $activity->log_name ?? 'Geral',
                'acao' => $activity->description,
                'dispositivo' => $properties['user_agent'] ?? $properties['device'] ?? 'Desconhecido',
                'ip' => $properties['ip'] ?? 'N/A',
            ];
        });
        
        // Dados para filtros
        $menus = Activity::select('log_name')->whereNotNull('log_name')->distinct()->pluck('log_name');
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();
        
        return view('logs.index', compact('logs', 'menus', 'users'));
    }
    
    public function export(Request $request)
    {
        $query = Activity::with('causer');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->latest()->get();
        
        $filename = 'logs_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        fputcsv($handle, ['Data', 'Hora', 'Utilizador', 'Menu', 'Acção', 'Dispositivo', 'IP']);
        
        foreach ($logs as $log) {
            $properties = is_string($log->properties) ? json_decode($log->properties, true) : [];
            
            fputcsv($handle, [
                $log->created_at->format('d/m/Y'),
                $log->created_at->format('H:i:s'),
                $log->causer?->name ?? 'Sistema',
                $log->log_name ?? 'Geral',
                $log->description,
                $properties['user_agent'] ?? $properties['device'] ?? 'Desconhecido',
                $properties['ip'] ?? 'N/A',
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    public function clearOldLogs(Request $request)
    {
        $days = $request->input('days', 90);
        $deleted = Activity::where('created_at', '<', now()->subDays($days))->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$deleted} logs mais antigos que {$days} dias foram removidos."
        ]);
    }
}