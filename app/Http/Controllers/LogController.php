<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view logs');
    }
    
    public function index(Request $request)
    {
        $query = Activity::with('causer')
                         ->orderBy('created_at', 'desc');
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id);
        }
        
        if ($request->has('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
            });
        }
        
        $logs = $query->paginate($request->get('per_page', 50));
        
        // Format logs for display
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'date' => $log->created_at->format('Y-m-d'),
                'time' => $log->created_at->format('H:i:s'),
                'user' => $log->causer ? $log->causer->name : 'Sistema',
                'user_email' => $log->causer ? $log->causer->email : null,
                'menu' => $log->log_name ?? 'Geral',
                'action' => $log->description,
                'subject_type' => class_basename($log->subject_type),
                'subject_id' => $log->subject_id,
                'properties' => $log->properties,
                'device' => $this->getDeviceInfo($log),
                'ip' => $log->properties['ip'] ?? null
            ];
        });
        
        return response()->json($logs);
    }
    
    public function filters()
    {
        $users = DB::table('users')->select('id', 'name')->orderBy('name')->get();
        $logNames = Activity::select('log_name')->distinct()->whereNotNull('log_name')->pluck('log_name');
        
        return response()->json([
            'users' => $users,
            'log_names' => $logNames
        ]);
    }
    
    public function export(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->get();
        
        $csvData = [];
        $csvData[] = ['Data', 'Hora', 'Utilizador', 'Menu', 'Ação', 'IP', 'Detalhes'];
        
        foreach ($logs as $log) {
            $csvData[] = [
                $log->created_at->format('Y-m-d'),
                $log->created_at->format('H:i:s'),
                $log->causer ? $log->causer->name : 'Sistema',
                $log->log_name ?? 'Geral',
                $log->description,
                $log->properties['ip'] ?? 'N/A',
                json_encode($log->properties)
            ];
        }
        
        $filename = "logs_" . now()->format('Y-m-d_His') . ".csv";
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    public function clearOldLogs(Request $request)
    {
        $this->middleware('permission:delete logs');
        
        $days = $request->get('days', 90);
        
        $deleted = Activity::where('created_at', '<', now()->subDays($days))->delete();
        
        return response()->json([
            'message' => "{$deleted} logs antigos foram eliminados"
        ]);
    }
    
    private function getDeviceInfo($log)
    {
        $properties = $log->properties;
        
        if (isset($properties['user_agent'])) {
            $userAgent = $properties['user_agent'];
            
            if (str_contains($userAgent, 'Mobile')) {
                return 'Mobile';
            } elseif (str_contains($userAgent, 'Tablet')) {
                return 'Tablet';
            } elseif (str_contains($userAgent, 'Windows') || str_contains($userAgent, 'Mac')) {
                return 'Desktop';
            }
        }
        
        return 'Desconhecido';
    }
}