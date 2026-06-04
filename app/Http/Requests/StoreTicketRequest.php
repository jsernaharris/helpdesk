<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'sometimes|in:incident,service_request,problem,change',
            'priority' => 'sometimes|in:critical,high,medium,low',
            'impact' => 'sometimes|in:extensive,significant,moderate,minor',
            'urgency' => 'sometimes|in:critical,high,medium,low',
            'organization_id' => 'sometimes|exists:organizations,id',
            'requester_user_id' => 'sometimes|exists:users,id',
            'assigned_to_user_id' => 'sometimes|nullable|exists:users,id',
            'assigned_to_team_id' => 'sometimes|nullable|exists:teams,id',
            'service_catalog_id' => 'sometimes|nullable|exists:service_catalogs,id',
            'source' => 'sometimes|in:email,portal,phone,chat,api,monitoring',
            'form_template_id' => 'sometimes|nullable|exists:form_templates,id',
            'custom_fields' => 'sometimes|array',
            'custom_fields.*' => 'nullable',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|max:25600',
        ];
    }
}
