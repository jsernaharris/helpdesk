<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:new,open,pending,on_hold,resolved,closed,cancelled',
            'priority' => 'sometimes|in:critical,high,medium,low',
            'impact' => 'sometimes|in:extensive,significant,moderate,minor',
            'urgency' => 'sometimes|in:critical,high,medium,low',
            'assigned_to_user_id' => 'sometimes|nullable|exists:users,id',
            'assigned_to_team_id' => 'sometimes|nullable|exists:teams,id',
            'service_catalog_id' => 'sometimes|nullable|exists:service_catalogs,id',
            'resolution' => 'sometimes|nullable|string',
        ];
    }
}
