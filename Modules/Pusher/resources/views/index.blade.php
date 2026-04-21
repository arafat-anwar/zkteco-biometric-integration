@extends('layouts.app')

@section('title', 'Pusher')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">Pusher — Device List</h1>

@forelse($organizations as $org)
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-700 mb-3">
        {{ $org->name }}
        <span class="text-sm font-normal text-gray-400 ml-2">{{ $org->devices->count() }} device(s)</span>
    </h2>

    @if($org->devices->isEmpty())
        <p class="text-sm text-gray-500 pl-4">No devices for this organization.</p>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($org->devices as $device)
        <div class="bg-white shadow rounded-lg p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $device->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $device->location ?? 'No location' }}</p>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full
                    {{ $device->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $device->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-sm text-gray-600 mb-1">IP: {{ $device->ip_address ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600 mb-3">Type: {{ strtoupper($device->connection_type) }}</p>
            @if($device->last_synced_at)
            <p class="text-xs text-gray-400 mb-3">Last synced: {{ $device->last_synced_at->diffForHumans() }}</p>
            @endif
            <a href="{{ route('pusher.device', $device->id) }}"
                class="block text-center bg-blue-600 hover:bg-blue-700 text-white text-sm py-1.5 rounded">
                Manage
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@empty
<div class="text-center py-12 text-gray-500">
    <p>No organizations. <a href="{{ route('organizations.create') }}" class="text-blue-600 hover:underline">Create one</a> and add devices first.</p>
</div>
@endforelse
@endsection
