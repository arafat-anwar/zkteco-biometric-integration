@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Organizations</p>
        <p class="text-3xl font-bold text-blue-600">{{ $orgCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Active Devices</p>
        <p class="text-3xl font-bold text-green-600">{{ $deviceCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Today's Entries</p>
        <p class="text-3xl font-bold text-purple-600">{{ $entryCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Today's Pushes</p>
        <p class="text-3xl font-bold text-orange-600">{{ $pushCount }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-700">Recent Push Logs</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pushed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saved</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentLogs as $log)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $log->organization->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $log->device->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $log->records_pushed }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $log->records_saved }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No push logs yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
