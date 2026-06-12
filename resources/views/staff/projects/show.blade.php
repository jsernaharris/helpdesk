@extends('layouts.staff')
@section('title', $project->project_number)

@php
$statusStyles = [
    'planned' => 'bg-gray-100 text-gray-700',
    'active' => 'bg-green-100 text-green-700',
    'on_hold' => 'bg-yellow-100 text-yellow-700',
    'completed' => 'bg-blue-100 text-blue-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
@endphp

@section('content')
@if(session('success'))
<div class="rounded-md bg-green-50 p-3 mb-4 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="rounded-md bg-red-50 p-3 mb-4 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $project->name }}</h2>
                        <span class="inline-block text-xs rounded px-2 py-0.5 {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $project->project_number }} · {{ $project->organization?->name }}</p>
                </div>
                <div class="flex gap-3">
                    @can('projects.update')
                    <a href="{{ route('staff.projects.edit', $project) }}" class="text-sm text-indigo-600 hover:underline">Edit</a>
                    @endcan
                    @can('projects.delete')
                    <form method="POST" action="{{ route('staff.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                    </form>
                    @endcan
                </div>
            </div>
            @if($project->description)
            <p class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $project->description }}</p>
            @endif
            <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><dt class="text-gray-500">Customer</dt><dd class="text-gray-900">{{ $project->customer_name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Customer Email</dt><dd class="text-gray-900">@if($project->customer_email)<a href="mailto:{{ $project->customer_email }}" class="text-indigo-600 hover:underline">{{ $project->customer_email }}</a>@else—@endif</dd></div>
                <div><dt class="text-gray-500">Start</dt><dd class="text-gray-900">{{ $project->start_date?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Due</dt><dd class="text-gray-900">{{ $project->due_date?->format('M d, Y') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Total Hours</dt><dd class="text-gray-900 font-semibold">{{ $project->totalHours() }}</dd></div>
                <div><dt class="text-gray-500">Created by</dt><dd class="text-gray-900">{{ $project->createdBy?->name ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- Time entries --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Time Entries</h3>
                @can('time.view_all')
                <a href="{{ route('staff.projects.time.export', ['project_id' => $project->id]) }}" class="text-sm text-indigo-600 hover:underline">Export CSV</a>
                @endcan
            </div>

            @can('time.log')
            <form method="POST" action="{{ route('staff.projects.time.store', $project) }}" class="px-5 py-4 border-b grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                @csrf
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500">Technician</label>
                    <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" @selected(auth()->id() == $tech->id)>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Date</label>
                    <input type="date" name="work_date" value="{{ now()->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Hours</label>
                    <input type="number" name="hours" step="0.25" min="0.25" max="24" placeholder="1.5" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Ticket (optional)</label>
                    <select name="ticket_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                        <option value="">— None —</option>
                        @foreach($tickets as $t)
                        <option value="{{ $t->id }}">{{ $t->ticket_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500">Notes</label>
                    <input type="text" name="notes" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-2 py-1.5 border">
                </div>
                <div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Log</button>
                </div>
            </form>
            @endcan

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Technician</th>
                        <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                        <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                        <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        <th class="px-5 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($project->timeEntries as $entry)
                    <tr>
                        <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $entry->work_date?->format('M d, Y') }}</td>
                        <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $entry->user?->name }}</td>
                        <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $entry->hours }}</td>
                        <td class="px-5 py-2 whitespace-nowrap text-sm text-indigo-600">{{ $entry->ticket?->ticket_number ?? '—' }}</td>
                        <td class="px-5 py-2 text-sm text-gray-500">{{ $entry->notes }}</td>
                        <td class="px-5 py-2 whitespace-nowrap text-right">
                            @if(auth()->user()->can('time.view_all') || $entry->user_id === auth()->id())
                            <form method="POST" action="{{ route('staff.projects.time.destroy', [$project, $entry]) }}" onsubmit="return confirm('Remove this entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500">No time logged yet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-5 py-2 text-sm font-semibold text-gray-700">Total</td>
                        <td class="px-5 py-2 text-sm font-semibold text-gray-900">{{ $project->totalHours() }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Work Ledger --}}
        @php
        $ledgerStyles = [
            'note' => 'bg-gray-100 text-gray-700',
            'created' => 'bg-blue-100 text-blue-700',
            'status_changed' => 'bg-indigo-100 text-indigo-700',
            'member_added' => 'bg-green-100 text-green-700',
            'member_removed' => 'bg-red-100 text-red-700',
            'time_logged' => 'bg-yellow-100 text-yellow-700',
        ];
        $ledgerLabels = [
            'note' => 'Note', 'created' => 'Created', 'status_changed' => 'Status',
            'member_added' => 'Member', 'member_removed' => 'Member', 'time_logged' => 'Time',
        ];
        @endphp
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b"><h3 class="font-semibold text-gray-900">Work Ledger</h3></div>

            @can('projects.update')
            <form method="POST" action="{{ route('staff.projects.ledger.store', $project) }}" class="px-5 py-4 border-b space-y-2">
                @csrf
                <textarea name="description" rows="2" required placeholder="Record work performed or a project update..." class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300"> Internal only (hidden from customer)
                    </label>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Add to Ledger</button>
                </div>
            </form>
            @endcan

            <ul class="divide-y divide-gray-200">
                @forelse($project->ledgerEntries as $entry)
                <li class="px-5 py-3 flex items-start gap-3">
                    <span class="mt-0.5 inline-block text-xs rounded px-2 py-0.5 {{ $ledgerStyles[$entry->type] ?? 'bg-gray-100 text-gray-700' }}">{{ $ledgerLabels[$entry->type] ?? ucfirst($entry->type) }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800">{{ $entry->description }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $entry->user?->name ?? 'System' }} · {{ $entry->created_at->format('M d, Y H:i') }}
                            @if($entry->is_internal)<span class="ml-1 text-amber-600">· internal</span>@endif
                        </p>
                    </div>
                    @if($entry->type === 'note')
                    @can('projects.update')
                    <form method="POST" action="{{ route('staff.projects.ledger.destroy', [$project, $entry]) }}" onsubmit="return confirm('Remove this note?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                    </form>
                    @endcan
                    @endif
                </li>
                @empty
                <li class="px-5 py-6 text-center text-sm text-gray-500">No ledger entries yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Members --}}
    <div>
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Team Members</h3>
            <ul class="space-y-2 mb-4">
                @forelse($project->members as $member)
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-900">{{ $member->name }} @if($member->pivot->is_lead)<span class="text-xs text-indigo-600">(lead)</span>@endif</span>
                    @can('projects.assign')
                    <form method="POST" action="{{ route('staff.projects.members.destroy', [$project, $member]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                    </form>
                    @endcan
                </li>
                @empty
                <li class="text-sm text-gray-500">No members assigned.</li>
                @endforelse
            </ul>
            @can('projects.assign')
            <form method="POST" action="{{ route('staff.projects.members.store', $project) }}" class="border-t pt-3 space-y-2">
                @csrf
                <select name="user_id" required class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">— Select technician —</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_lead" value="1" class="rounded border-gray-300"> Lead
                </label>
                <button type="submit" class="block w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Add Member</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
