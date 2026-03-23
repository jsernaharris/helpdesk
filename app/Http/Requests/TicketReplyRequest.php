<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => 'required|string',
            'is_internal' => 'sometimes|boolean',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|max:25600',
        ];
    }
}
