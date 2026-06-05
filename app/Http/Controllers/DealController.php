<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use App\Mail\ProposalMail;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DealController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenant = $this->tenantService->getActiveTenant();

        $deals = Deal::with(['entity', 'person', 'owner'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Traduzir as etapas
        $deals->transform(function ($deal) {
            $deal->stage_label = translateStage($deal->stage);
            return $deal;
        });

        return view('deals.index', compact('deals'));
    }

    public function kanban(Request $request)
    {
        $tenant = $this->tenantService->getActiveTenant();

        $query = Deal::with(['entity', 'person', 'owner'])
            ->where('tenant_id', $tenant->id);

        // Filtros
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('expected_close_date')) {
            $query->whereDate('expected_close_date', $request->expected_close_date);
        }
        if ($request->filled('min_value')) {
            $query->where('value', '>=', $request->min_value);
        }
        if ($request->filled('max_value')) {
            $query->where('value', '<=', $request->max_value);
        }

        $deals = $query->get();
        $stages = ['lead', 'proposal', 'negotiation', 'follow_up', 'won', 'lost'];

        $stageLabels = [
            'lead' => 'Potencial',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'follow_up' => 'Atualização',
            'won' => 'Ganho',
            'lost' => 'Perdido'
        ];

        $users = \App\Models\User::where('tenant_id', $tenant->id)->get();

        return view('deals.kanban', compact('deals', 'stages', 'stageLabels', 'users'));
    }

    public function create()
    {
        $tenant = $this->tenantService->getActiveTenant();

        $entities = Entity::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $people = Person::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $users = User::where('tenant_id', $tenant->id)->get();

        return view('deals.create', compact('entities', 'people', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'entity_id' => 'nullable|exists:entities,id',
            'person_id' => 'nullable|exists:people,id',
            'value' => 'nullable|numeric|min:0',
            'stage' => 'required|in:lead,proposal,negotiation,follow_up,won,lost',
            'expected_close_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $deal = Deal::create([
            'entity_id' => $request->entity_id,
            'person_id' => $request->person_id,
            'title' => $request->title,
            'value' => $request->value ?? 0,
            'stage' => $request->stage,
            'probability' => $request->probability ?? 0,
            'expected_close_date' => $request->expected_close_date,
            'owner_id' => auth()->id(),
            'tenant_id' => tenant()->id,
        ]);

        return redirect()->route('deals.show', $deal->id)->with('success', 'Negócio criado com sucesso!');
    }

    public function show(Deal $deal)
    {
        $deal->load([
            'entity',
            'person',
            'owner',
            'activities' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        $users = \App\Models\User::where('tenant_id', tenant()->id)->get();

        return view('deals.show', compact('deal', 'users'));
    }

    public function edit(Deal $deal)
    {
        $entities = Entity::where('tenant_id', tenant()->id)->orderBy('name')->get();
        $people = Person::where('tenant_id', tenant()->id)->orderBy('name')->get();
        $users = \App\Models\User::where('tenant_id', tenant()->id)->get();

        $stageLabels = [
            'lead' => 'Potencial',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'follow_up' => 'Atualização',
            'won' => 'Ganho',
            'lost' => 'Perdido'
        ];

        return view('deals.edit', compact('deal', 'entities', 'people', 'users', 'stageLabels'));
    }

    public function update(Request $request, Deal $deal)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'stage' => 'required|in:lead,proposal,negotiation,follow_up,won,lost',
            'expected_close_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $deal->update([
            'entity_id' => $request->entity_id,
            'person_id' => $request->person_id,
            'title' => $request->title,
            'value' => $request->value ?? 0,
            'stage' => $request->stage,
            'probability' => $request->probability ?? 0,
            'expected_close_date' => $request->expected_close_date,
        ]);

        return redirect()->route('deals.index')->with('success', 'Negócio atualizado com sucesso!');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Negócio removido com sucesso!');
    }

    public function move(Request $request, Deal $deal)
    {
        $request->validate([
            'stage' => 'required|in:lead,proposal,negotiation,follow_up,won,lost',
        ]);

        $deal->update(['stage' => $request->stage]);

        return response()->json(['success' => true]);
    }

    public function sendProposal(Request $request, Deal $deal)
    {
        $request->validate([
            'proposal_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'recipient_email' => 'required|email',
        ]);

        $path = $request->file('proposal_file')->store('proposals', 'public');

        $deal->update([
            'proposal_file' => $path,
            'proposal_sent_at' => now(),
            'proposal_sent_by' => auth()->id(),
            'proposal_sent_to' => $request->recipient_email,
            'proposal_email_body' => $request->email_message ?? null,
            'proposal_status' => 'sent',
        ]);

        try {
            Mail::to($request->recipient_email)->send(new ProposalMail($deal, $request->email_message));

            $deal->activities()->create([
                'type' => 'email',
                'description' => "Proposta enviada para {$request->recipient_email}",
                'user_id' => auth()->id(),
                'scheduled_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Proposta enviada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar proposta: ' . $e->getMessage());
        }
    }

    public function convertToInvoice(Deal $deal)
    {
        $invoice = Invoice::create([
            'deal_id' => $deal->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'amount' => $deal->value,
            'status' => 'draft',
            'tenant_id' => $deal->tenant_id,
        ]);

        $deal->update(['stage' => 'won']);
        $deal->activities()->create([
            'type' => 'invoice',
            'description' => "Fatura {$invoice->invoice_number} gerada",
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Fatura gerada com sucesso!');
    }

    public function activateFollowUp(Request $request, Deal $deal)
    {
        $deal->activateFollowUp();
        return redirect()->back()->with('success', 'Follow-up ativado com sucesso!');
    }

    public function cancelFollowUp(Request $request, Deal $deal)
    {
        $deal->deactivateFollowUp();
        return redirect()->back()->with('success', 'Follow-up cancelado com sucesso!');
    }

    public function storeActivity(Request $request, Deal $deal)
    {
        $request->validate([
            'type' => 'required|in:call,task,meeting,note',
            'scheduled_at' => 'nullable|date',
            'description' => 'required|string',
        ]);

        $activity = $deal->activities()->create([
            'type' => $request->type,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at ?? now(),
            'user_id' => $request->user_id ?? auth()->id(),
            'subject' => $request->subject ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Atividade criada com sucesso!',
            'activity' => $activity->load('user')
        ]);
    }

    public function updateActivity(Request $request, Deal $deal, $activityId)
    {
        $activity = $deal->activities()->findOrFail($activityId);

        $request->validate([
            'type' => 'required|in:call,task,meeting,note',
            'description' => 'required|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $activity->update([
            'type' => $request->type,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at ?? $activity->scheduled_at,
            'user_id' => $request->user_id ?? auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Atividade atualizada com sucesso!',
            'activity' => $activity->load('user')
        ]);
    }

    public function destroyActivity(Deal $deal, $activityId)
    {
        $activity = $deal->activities()->findOrFail($activityId);
        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Atividade eliminada com sucesso!'
        ]);
    }
}
