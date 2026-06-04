<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\Organization;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::with(['organization', 'createdBy'])
            ->withCount('tickets')
            ->orderByDesc('updated_at')
            ->paginate(25);

        return view('staff.form-templates.index', compact('templates'));
    }

    public function create()
    {
        $organizations = Organization::where('is_active', true)
            ->where('is_msp', false)
            ->orderBy('name')
            ->get();

        return view('staff.form-templates.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_active' => 'boolean',
            'fields' => 'required|json',
        ]);

        $fields = json_decode($data['fields'], true);
        if (empty($fields)) {
            return back()->withErrors(['fields' => 'At least one field is required.'])->withInput();
        }

        FormTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'fields' => $fields,
            'created_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('staff.form-templates.index')
            ->with('success', 'Form template created successfully.');
    }

    public function show(FormTemplate $formTemplate)
    {
        $formTemplate->load(['organization', 'createdBy']);

        return view('staff.form-templates.show', compact('formTemplate'));
    }

    public function edit(FormTemplate $formTemplate)
    {
        $organizations = Organization::where('is_active', true)
            ->where('is_msp', false)
            ->orderBy('name')
            ->get();

        return view('staff.form-templates.edit', compact('formTemplate', 'organizations'));
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_active' => 'boolean',
            'fields' => 'required|json',
        ]);

        $fields = json_decode($data['fields'], true);
        if (empty($fields)) {
            return back()->withErrors(['fields' => 'At least one field is required.'])->withInput();
        }

        $formTemplate->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'fields' => $fields,
        ]);

        return redirect()->route('staff.form-templates.index')
            ->with('success', 'Form template updated successfully.');
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();

        return redirect()->route('staff.form-templates.index')
            ->with('success', 'Form template deleted.');
    }
}
