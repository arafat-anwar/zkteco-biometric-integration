@extends('layouts.app')

@section('title', 'Attendance Entries')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Attendance Entries</h1>
    <a href="{{ route('receiver.logs') }}" class="text-blue-600 text-sm hover:underline">View Push Logs &rarr;</a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('receiver.index') }}" class="bg-white shadow rounded-lg p-4 mb-6">
    <div class="flex flex-wrap gap-3">
        <select name="organization_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" placeholder="From"
            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}" placeholder="To"
            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        <input type="text" name="emp_id" value="{{ request('emp_id') }}" placeholder="Employee ID"
            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm">Filter</button>
        <a href="{{ route('receiver.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-1.5 rounded text-sm">Clear</a>
    </div>
</form>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b text-sm text-gray-600">
        {{ $entries->total() }} record(s) found
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emp ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Real Emp ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($entries as $entry)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $entry->emp_id }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry->real_emp_id ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $entry->check_time }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry->organization->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry->device->name ?? $entry->device_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry->branch ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">No entries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">{{ $entries->links() }}</div>
</div>
@endsection
