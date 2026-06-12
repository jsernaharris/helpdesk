<?php

namespace App\Http\Requests;

use App\Models\EmailMailbox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isMspAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'driver' => ['required', Rule::in([EmailMailbox::DRIVER_IMAP, EmailMailbox::DRIVER_GRAPH])],

            // IMAP / SMTP (required only when using the IMAP driver).
            'imap_host' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string|max:255',
            'imap_port' => 'nullable|integer',
            'imap_encryption' => ['nullable', Rule::in(['ssl', 'tls', 'none'])],
            'imap_username' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string|max:255',
            'imap_password' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string',
            'smtp_host' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => ['nullable', Rule::in(['ssl', 'tls', 'none'])],
            'smtp_username' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string|max:255',
            'smtp_password' => 'required_if:driver,'.EmailMailbox::DRIVER_IMAP.'|nullable|string',

            // Microsoft Graph (required only when using the Graph driver).
            'graph_tenant_id' => 'required_if:driver,'.EmailMailbox::DRIVER_GRAPH.'|nullable|string|max:255',
            'graph_client_id' => 'required_if:driver,'.EmailMailbox::DRIVER_GRAPH.'|nullable|string|max:255',
            'graph_client_secret' => 'required_if:driver,'.EmailMailbox::DRIVER_GRAPH.'|nullable|string',
            'graph_user_id' => 'required_if:driver,'.EmailMailbox::DRIVER_GRAPH.'|nullable|string|max:255',

            'is_active' => 'sometimes|boolean',
            'auto_create_tickets' => 'sometimes|boolean',
            'default_priority' => ['nullable', Rule::in(['critical', 'high', 'medium', 'low'])],
            'default_type' => ['nullable', Rule::in(['incident', 'service_request'])],
            'queue_id' => 'nullable|exists:queues,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('queue_id')) {
                $queue = \App\Models\Queue::find($this->input('queue_id'));
                if ($queue && (int) $queue->organization_id !== (int) $this->input('organization_id')) {
                    $validator->errors()->add('queue_id', 'The selected queue belongs to a different organization.');
                }
            }
        });
    }
}
