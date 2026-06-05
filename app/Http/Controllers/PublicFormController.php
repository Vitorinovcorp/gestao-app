<?php

namespace App\Http\Controllers;

use App\Models\PublicForm;
use App\Models\FormSubmission;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicFormController extends Controller
{
    public function index()
    {
        $forms = PublicForm::where('tenant_id', tenant()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public-forms.index', compact('forms'));
    }

    public function create()
    {
        return view('public-forms.create');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.id' => 'nullable|string', 
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,email,phone,textarea,select',
            'fields.*.required' => 'boolean',
            'confirmation_message' => 'nullable|string',
            'success_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slug = Str::slug($request->title) . '-' . Str::random(6);
        $embedCode = 'form_' . Str::random(20);

        $form = PublicForm::create([
            'title' => $request->title,
            'slug' => $slug,
            'fields' => $request->fields,
            'embed_code' => $embedCode,
            'confirmation_message' => $request->confirmation_message ?? 'Obrigado! O seu formulário foi submetido com sucesso.',
            'success_url' => $request->success_url,
            'is_active' => true,
            'tenant_id' => tenant()->id,
        ]);

        return redirect()->route('public-forms.index')->with('success', 'Formulário criado com sucesso!');
    }
    public function show($embedCode)
    {
        $form = PublicForm::where('embed_code', $embedCode)
            ->where('is_active', true)
            ->firstOrFail();

        return view('public-forms.show', compact('form'));
    }

    public function submit(Request $request, $embedCode)
    {
        $form = PublicForm::where('embed_code', $embedCode)
            ->where('is_active', true)
            ->firstOrFail();

        // Validar campos do formulário
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldName = 'field_' . $field['id'] ?? Str::slug($field['label']);
            $rules[$fieldName] = ($field['required'] ?? false) ? 'required' : 'nullable';
            if ($field['type'] === 'email') {
                $rules[$fieldName] .= '|email';
            }
            if ($field['type'] === 'phone') {
                $rules[$fieldName] .= '|string|max:20';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $submission = FormSubmission::create([
            'public_form_id' => $form->id,
            'data' => $request->except(['_token', 'g-recaptcha-response']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        // Criar lead (entidade)
        $leadData = [];
        foreach ($form->fields as $field) {
            $fieldName = 'field_' . $field['id'] ?? Str::slug($field['label']);
            if ($request->has($fieldName)) {
                $leadData[$field['label']] = $request->$fieldName;
            }
        }

        $nif = '999' . date('Ymd') . rand(100, 999);

        Entity::create([
            'tenant_id' => $form->tenant_id,
            'name' => $leadData['Nome'] ?? $leadData['name'] ?? 'Lead via Formulário',
            'type' => 'client',
            'nif' => $nif,
            'email' => $leadData['Email'] ?? $leadData['email'] ?? null,
            'phone' => $leadData['Telefone'] ?? $leadData['phone'] ?? null,
            'is_active' => true,
            'observations' => "Lead gerada pelo formulário: {$form->title}",
        ]);

        // Redirecionar ou mostrar mensagem
        if ($form->success_url) {
            return redirect($form->success_url);
        }

        return back()->with('success', $form->confirmation_message);
    }

    public function edit(PublicForm $form)
    {
        return view('public-forms.edit', compact('form'));
    }

    public function update(Request $request, PublicForm $form)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,email,phone,textarea,select',
            'fields.*.required' => 'boolean',
            'confirmation_message' => 'nullable|string',
            'success_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $form->update($request->all());

        return redirect()->route('public-forms.index')->with('success', 'Formulário atualizado com sucesso!');
    }

    public function destroy(PublicForm $form)
    {
        $form->delete();
        return redirect()->route('public-forms.index')->with('success', 'Formulário eliminado com sucesso!');
    }
}
