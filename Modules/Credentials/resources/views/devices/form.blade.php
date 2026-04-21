@extends('layouts.app')

@section('title', isset($device) ? 'Edit Device' : 'Create Device')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($device) ? 'Edit Device' : 'New Device' }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ isset($device) ? route('devices.update', $device) : route('devices.store') }}">
            @csrf
            @if(isset($device)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device Name *</label>
                    <input type="text" name="name" value="{{ old('name', $device->name ?? '') }}" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                    <select name="organization_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}"
                            {{ old('organization_id', $device->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $device->ip_address ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                    <input type="number" name="port" value="{{ old('port', $device->port ?? 4370) }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Connection Type</label>
                    <select name="connection_type"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="tcp" {{ old('connection_type', $device->connection_type ?? 'tcp') === 'tcp' ? 'selected' : '' }}>TCP</option>
                        <option value="odbc" {{ old('connection_type', $device->connection_type ?? '') === 'odbc' ? 'selected' : '' }}>ODBC (MDB File)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $device->location ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $device->serial_number ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <input type="text" name="model" value="{{ old('model', $device->model ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">MDB File Path</label>
                <input type="text" name="mdb_path" value="{{ old('mdb_path', $device->mdb_path ?? '') }}"
                    placeholder="e.g. C:\ATT2000\ATT2000.mdb"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Required when using ODBC connection type.</p>
            </div>

            <div class="mb-6 flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $device->is_active ?? true) ? 'checked' : '' }} class="mr-2">
                <label for="is_active" class="text-sm text-gray-700">Active</label>
            </div>

            <div class="flex space-x-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    {{ isset($device) ? 'Update Device' : 'Create Device' }}
                </button>
                <a href="{{ route('devices.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
