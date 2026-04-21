@extends('layouts.app')

@section('title', 'Device: ' . $device->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $device->name }}</h1>
        <p class="text-sm text-gray-500">{{ $device->organization->name }} &mdash; {{ $device->location }}</p>
    </div>
    <a href="{{ route('pusher.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm">
        &larr; Back
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Device Info --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 mb-3">Device Info</h2>
        <dl class="space-y-2 text-sm">
            <div><dt class="text-xs text-gray-400">IP</dt><dd>{{ $device->ip_address ?? 'N/A' }}</dd></div>
            <div><dt class="text-xs text-gray-400">Port</dt><dd>{{ $device->port ?? 'N/A' }}</dd></div>
            <div><dt class="text-xs text-gray-400">Connection</dt><dd>{{ strtoupper($device->connection_type) }}</dd></div>
            <div><dt class="text-xs text-gray-400">Serial</dt><dd>{{ $device->serial_number ?? 'N/A' }}</dd></div>
            <div><dt class="text-xs text-gray-400">Model</dt><dd>{{ $device->model ?? 'N/A' }}</dd></div>
            @if($device->mdb_path)
            <div><dt class="text-xs text-gray-400">MDB Path</dt><dd class="break-all">{{ $device->mdb_path }}</dd></div>
            @endif
            <div><dt class="text-xs text-gray-400">Last Synced</dt>
                <dd>{{ $device->last_synced_at ? $device->last_synced_at->format('Y-m-d H:i') : 'Never' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Actions --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-sm font-medium text-gray-500 mb-4">Push Actions</h2>

            {{-- Preview --}}
            <form method="POST" action="{{ route('pusher.preview', $device->id) }}" class="mb-4">
                @csrf
                <div class="flex items-end space-x-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From Date</label>
                        <input type="date" name="from" value="{{ date('Y-m-d', strtotime('-7 days')) }}"
                            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To Date</label>
                        <input type="date" name="to" value="{{ date('Y-m-d') }}"
                            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-1.5 rounded text-sm">
                        Preview MDB Data
                    </button>
                </div>
            </form>

            {{-- Push --}}
            <form method="POST" action="{{ route('pusher.push', $device->id) }}">
                @csrf
                <div class="flex items-end space-x-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From Date</label>
                        <input type="date" name="from" value="{{ date('Y-m-d', strtotime('-7 days')) }}"
                            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To Date</label>
                        <input type="date" name="to" value="{{ date('Y-m-d') }}"
                            class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm"
                        onclick="return confirm('Push attendance data to receiver?')">
                        Push to Receiver
                    </button>
                </div>
            </form>
        </div>

        {{-- Recent push logs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-sm font-medium text-gray-500 mb-3">Recent Push Logs</h2>
            @if($pushLogs->isEmpty())
                <p class="text-sm text-gray-500">No push logs yet.</p>
            @else
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead><tr>
                    <th class="pb-2 text-left text-xs text-gray-400">Date</th>
                    <th class="pb-2 text-left text-xs text-gray-400">Pushed</th>
                    <th class="pb-2 text-left text-xs text-gray-400">Saved</th>
                    <th class="pb-2 text-left text-xs text-gray-400">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($pushLogs as $log)
                    <tr>
                        <td class="py-2 text-gray-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-2">{{ $log->records_pushed }}</td>
                        <td class="py-2">{{ $log->records_saved }}</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
