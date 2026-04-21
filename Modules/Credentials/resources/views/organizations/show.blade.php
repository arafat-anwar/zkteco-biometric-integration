@extends('layouts.app')

@section('title', $organization->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $organization->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $organization->email }}</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('organizations.edit', $organization) }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Edit
        </a>
        <a href="{{ route('organizations.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
            Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 mb-3">Details</h2>
        <dl class="space-y-2">
            <div><dt class="text-xs text-gray-500">Slug</dt><dd class="text-sm">{{ $organization->slug }}</dd></div>
            <div><dt class="text-xs text-gray-500">Phone</dt><dd class="text-sm">{{ $organization->phone ?? '-' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Address</dt><dd class="text-sm">{{ $organization->address ?? '-' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Status</dt>
                <dd><span class="px-2 py-0.5 text-xs rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $organization->is_active ? 'Active' : 'Inactive' }}
                </span></dd>
            </div>
        </dl>
    </div>

    <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
        <h2 class="text-sm font-medium text-gray-500 mb-3">Devices ({{ $organization->devices->count() }})</h2>
        @if($organization->devices->isEmpty())
            <p class="text-sm text-gray-500">No devices registered.</p>
        @else
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead><tr>
                <th class="pb-2 text-left text-xs text-gray-500">Name</th>
                <th class="pb-2 text-left text-xs text-gray-500">IP</th>
                <th class="pb-2 text-left text-xs text-gray-500">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($organization->devices as $device)
                <tr>
                    <td class="py-2">{{ $device->name }}</td>
                    <td class="py-2 text-gray-500">{{ $device->ip_address }}</td>
                    <td class="py-2">
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $device->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $device->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
