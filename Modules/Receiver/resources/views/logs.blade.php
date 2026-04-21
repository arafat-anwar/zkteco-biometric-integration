@extends('layouts.app')

@section('title', 'Push Logs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Push Logs</h1>
    <a href="{{ route('receiver.index') }}" class="text-blue-600 text-sm hover:underline">&larr; Attendance Entries</a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pushed</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saved</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duplicates</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pusher IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-gray-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ $log->organization->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $log->device->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $log->records_pushed }}</td>
                    <td class="px-4 py-3 text-green-700 font-medium">{{ $log->records_saved }}</td>
                    <td class="px-4 py-3 text-orange-600">{{ $log->duplicates_skipped }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($log->status) }}
                        </span>
                        @if($log->message)
                        <span class="text-xs text-gray-500 ml-1">{{ $log->message }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->pusher_ip }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">No push logs yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">{{ $logs->links() }}</div>
</div>
@endsection
