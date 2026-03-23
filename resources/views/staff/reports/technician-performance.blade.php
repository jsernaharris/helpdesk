@extends('layouts.staff')
@section('title', 'Technician Performance Report')

@section('content')
<form method="GET" class="flex gap-3 mb-6">
    <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
    <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Apply</button>
</form>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technician</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resolved</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Resolution (hrs)</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SLA Breaches</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($performance as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->tech_name }}</td>
                <td class="px-4 py-3">{{ $row->total_assigned }}</td>
                <td class="px-4 py-3 text-green-600">{{ $row->resolved }}</td>
                <td class="px-4 py-3">{{ $row->avg_resolution_minutes ? round($row->avg_resolution_minutes / 60, 1) : '-' }}</td>
                <td class="px-4 py-3 text-red-600">{{ $row->sla_breaches }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
