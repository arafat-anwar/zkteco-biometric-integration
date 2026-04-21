@extends('layouts.app')

@section('title', 'Preview: ' . $device->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">MDB Preview</h1>
        <p class="text-sm text-gray-500">{{ $device->name }} &mdash; {{ $fromDate }} to {{ $toDate }}</p>
    </div>
    <div class="flex space-x-3">
        <form method="POST" action="{{ route('pusher.push', $device->id) }}">
            @csrf
            <input type="hidden" name="from" value="{{ $fromDate }}">
            <input type="hidden" name="to" value="{{ $toDate }}">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                onclick="return confirm('Push {{ count($entries) }} entries to receiver?')">
                Push {{ count($entries) }} Entries
            </button>
        </form>
        <a href="{{ route('pusher.device', $device->id) }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm">
            Back
        </a>
    </div>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
        Found <strong>{{ count($entries) }}</strong> record(s) in MDB file.
        @if(!empty($error))
        <span class="text-red-600 ml-2">{{ $error }}</span>
        @endif
    </div>
    <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emp ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Real Emp ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($entries as $i => $entry)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                    <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 font-medium">{{ $entry['emp_id'] }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $entry['real_emp_id'] ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $entry['check_time'] }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $entry['device_name'] ?? $device->name }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $entry['branch'] ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No records found in the specified date range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
