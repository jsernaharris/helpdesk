@php($m = $mailbox ?? null)
@php($currentDriver = old('driver', $m->driver ?? 'imap'))

@if($errors->any())
<div class="rounded-md bg-red-50 p-4 mb-4">
    <ul class="list-disc list-inside text-sm text-red-800">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white shadow rounded-lg p-6 space-y-5">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $m->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" name="email_address" value="{{ old('email_address', $m->email_address ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="support@company.com">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">— None (MSP default) —</option>
                @foreach($organizations as $org)
                <option value="{{ $org->id }}" @selected(old('organization_id', $m->organization_id ?? '') == $org->id)>{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Driver</label>
            <select name="driver" id="driver" onchange="toggleDriverFields()" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="imap" @selected($currentDriver === 'imap')>IMAP / SMTP (Basic Auth)</option>
                <option value="microsoft_graph" @selected($currentDriver === 'microsoft_graph')>Microsoft Graph (Microsoft 365 / shared mailbox)</option>
            </select>
        </div>
    </div>

    {{-- IMAP / SMTP fields --}}
    <div id="imap-fields" class="space-y-5 border-t pt-5">
        <h3 class="text-sm font-semibold text-gray-900">IMAP (inbound)</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">IMAP Host</label>
                <input type="text" name="imap_host" value="{{ old('imap_host', $m->imap_host ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="outlook.office365.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Port</label>
                <input type="number" name="imap_port" value="{{ old('imap_port', $m->imap_port ?? 993) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Encryption</label>
                <select name="imap_encryption" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['ssl', 'tls', 'none'] as $enc)
                    <option value="{{ $enc }}" @selected(old('imap_encryption', $m->imap_encryption ?? 'ssl') === $enc)>{{ strtoupper($enc) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="imap_username" value="{{ old('imap_username', $m->imap_username ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="imap_password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="{{ $m ? '•••••• (unchanged)' : '' }}">
            </div>
        </div>

        <h3 class="text-sm font-semibold text-gray-900">SMTP (outbound)</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                <input type="text" name="smtp_host" value="{{ old('smtp_host', $m->smtp_host ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="smtp.office365.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Port</label>
                <input type="number" name="smtp_port" value="{{ old('smtp_port', $m->smtp_port ?? 587) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Encryption</label>
                <select name="smtp_encryption" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['ssl', 'tls', 'none'] as $enc)
                    <option value="{{ $enc }}" @selected(old('smtp_encryption', $m->smtp_encryption ?? 'tls') === $enc)>{{ strtoupper($enc) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="smtp_username" value="{{ old('smtp_username', $m->smtp_username ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="smtp_password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="{{ $m ? '•••••• (unchanged)' : '' }}">
            </div>
        </div>
    </div>

    {{-- Microsoft Graph fields --}}
    <div id="graph-fields" class="space-y-5 border-t pt-5">
        <h3 class="text-sm font-semibold text-gray-900">Microsoft Graph (app-only)</h3>
        <p class="text-xs text-gray-500">Uses an Azure app registration with application permissions <code>Mail.ReadWrite</code> and <code>Mail.Send</code> (admin-consented). Credentials are stored encrypted in the database.</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tenant ID</label>
                <input type="text" name="graph_tenant_id" value="{{ old('graph_tenant_id', $m->graph_tenant_id ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="00000000-0000-0000-0000-000000000000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Client ID (Application ID)</label>
                <input type="text" name="graph_client_id" value="{{ old('graph_client_id', $m->graph_client_id ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Client Secret</label>
                <input type="password" name="graph_client_secret" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="{{ $m ? '•••••• (unchanged)' : '' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Mailbox (UPN or address)</label>
                <input type="text" name="graph_user_id" value="{{ old('graph_user_id', $m->graph_user_id ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="shared-inbox@company.com">
            </div>
        </div>
    </div>

    {{-- Behavior --}}
    <div class="grid grid-cols-2 gap-4 border-t pt-5">
        <div>
            <label class="block text-sm font-medium text-gray-700">Default Priority</label>
            <select name="default_priority" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach(['critical', 'high', 'medium', 'low'] as $p)
                <option value="{{ $p }}" @selected(old('default_priority', $m->default_priority ?? 'medium') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Default Type</label>
            <select name="default_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                @foreach(['incident' => 'Incident', 'service_request' => 'Service Request'] as $val => $label)
                <option value="{{ $val }}" @selected(old('default_type', $m->default_type ?? 'incident') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Default Queue</label>
            <select name="queue_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">— None —</option>
                @foreach($queues as $orgQueues)
                <optgroup label="{{ $orgQueues->first()->organization?->name ?? 'Unassigned' }}">
                    @foreach($orgQueues as $queue)
                    <option value="{{ $queue->id }}" @selected(old('queue_id', $m->queue_id ?? '') == $queue->id)>{{ $queue->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Tickets created from this mailbox land in this queue. Must belong to the selected organization.</p>
        </div>
    </div>
    <div class="flex gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m->is_active ?? true)) class="rounded border-gray-300"> Active
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="auto_create_tickets" value="1" @checked(old('auto_create_tickets', $m->auto_create_tickets ?? true)) class="rounded border-gray-300"> Auto-create tickets
        </label>
    </div>

    <div class="flex justify-end gap-3 border-t pt-5">
        <a href="{{ route('staff.mailboxes.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $m ? 'Save Changes' : 'Create Mailbox' }}</button>
    </div>
</div>

<script>
    function toggleDriverFields() {
        var driver = document.getElementById('driver').value;
        document.getElementById('imap-fields').style.display = driver === 'imap' ? '' : 'none';
        document.getElementById('graph-fields').style.display = driver === 'microsoft_graph' ? '' : 'none';
    }
    toggleDriverFields();
</script>
