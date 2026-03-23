@extends('layouts.staff')
@section('title', 'SLA Compliance Report')

@section('content')
<form method="GET" class="flex gap-3 mb-6">
    <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Apply</button>
</form>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">By Priority</h3>
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Priority</th><th class="pb-2">Total</th><th class="pb-2">Response Breaches</th><th class="pb-2">Resolution Breaches</th></tr></thead>
            <tbody>
                @foreach($stats as $row)
                <tr class="border-t"><td class="py-2">@include('components.priority-badge', ['priority' => $row->priority])</td><td>{{ $row->total }}</td><td class="text-red-600">{{ $row->response_breaches }}</td><td class="text-red-600">{{ $row->resolution_breaches }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">By Organization</h3>
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="pb-2">Organization</th><th class="pb-2">Total</th><th class="pb-2">Response Breaches</th><th class="pb-2">Resolution Breaches</th></tr></thead>
            <tbody>
                @foreach($byOrg as $row)
                <tr class="border-t"><td class="py-2">{{ $row->org_name }}</td><td>{{ $row->total }}</td><td class="text-red-600">{{ $row->response_breaches }}</td><td class="text-red-600">{{ $row->resolution_breaches }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
