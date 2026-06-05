<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AutomationRuleController extends Controller
{
    public function index()
    {
        $rules = AutomationRule::where('tenant_id', tenant()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $priorityMap = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta'
        ];

        $stageMap = [
            'lead' => 'Lead',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'follow_up' => 'Follow Up',
            'won' => 'Ganho',
            'lost' => 'Perdido'
        ];

        $rules->transform(function ($rule) use ($priorityMap, $stageMap) {
            $rule->priority_label = $priorityMap[$rule->action_config['priority'] ?? 'medium'] ?? 'Média';

            if ($rule->trigger_type === 'stage_change') {
                $from = $rule->conditions['from_stage'] ?? '';
                $to = $rule->conditions['to_stage'] ?? '';
                $rule->from_label = $stageMap[$from] ?? ucfirst($from);
                $rule->to_label = $stageMap[$to] ?? ucfirst($to);
            }

            return $rule;
        });

        return view('automation.index', compact('rules'));
    }

    public function create()
    {
        return view('automation.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:inactivity_days,stage_change,value_threshold,days_until_close',
            'days' => 'required_if:trigger_type,inactivity_days,days_until_close|integer|min:1',
            'stage' => 'nullable|string',
            'value_threshold' => 'nullable|numeric|min:0',
            'activity_type' => 'required|in:call,task,meeting,note',
            'priority' => 'required|in:low,medium,high',
            'days_offset' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $conditions = [];
        switch ($request->trigger_type) {
            case 'inactivity_days':
            case 'days_until_close':
                $conditions['days'] = $request->days;
                if ($request->stage) {
                    $conditions['stage'] = $request->stage;
                }
                break;
            case 'value_threshold':
                $conditions['value_threshold'] = $request->value_threshold;
                break;
            case 'stage_change':
                $conditions['from_stage'] = $request->from_stage;
                $conditions['to_stage'] = $request->to_stage;
                break;
        }

        AutomationRule::create([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'conditions' => $conditions,
            'action_type' => 'create_activity',
            'action_config' => [
                'activity_type' => $request->activity_type,
                'priority' => $request->priority,
                'days_offset' => $request->days_offset,
                'description' => $request->description,
            ],
            'tenant_id' => tenant()->id,
            'is_active' => true,
        ]);

        return redirect()->route('automation.index')->with('success', 'Regra criada com sucesso!');
    }
    public function edit(AutomationRule $rule)
    {
        return view('automation.edit', compact('rule'));
    }

    public function update(Request $request, AutomationRule $rule)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:inactivity_days,stage_change,value_threshold,days_until_close',
            'days' => 'required_if:trigger_type,inactivity_days,days_until_close|integer|min:1',
            'stage' => 'nullable|string',
            'value_threshold' => 'required_if:trigger_type,value_threshold|numeric|min:0',
            'activity_type' => 'required|in:call,task,meeting,note',
            'priority' => 'required|in:low,medium,high',
            'days_offset' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $conditions = [];
        switch ($request->trigger_type) {
            case 'inactivity_days':
            case 'days_until_close':
                $conditions['days'] = $request->days;
                if ($request->stage) {
                    $conditions['stage'] = $request->stage;
                }
                break;
            case 'value_threshold':
                $conditions['value_threshold'] = $request->value_threshold;
                break;
            case 'stage_change':
                $conditions['from_stage'] = $request->from_stage;
                $conditions['to_stage'] = $request->to_stage;
                break;
        }

        $rule->update([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'conditions' => $conditions,
            'action_config' => [
                'activity_type' => $request->activity_type,
                'priority' => $request->priority,
                'days_offset' => $request->days_offset,
                'description' => $request->description,
            ],
        ]);

        return redirect()->route('automation.index')->with('success', 'Regra atualizada com sucesso!');
    }
    public function destroy(AutomationRule $rule)
    {
        $rule->delete();
        return redirect()->route('automation.index')->with('success', 'Regra eliminada com sucesso!');
    }

    public function toggleStatus(AutomationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        $status = $rule->is_active ? 'ativada' : 'desativada';
        return redirect()->back()->with('success', "Regra {$status} com sucesso!");
    }
}
