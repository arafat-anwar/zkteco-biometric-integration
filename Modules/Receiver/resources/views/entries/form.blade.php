@extends('layouts.app')

@section('title', isset($entry) ? 'Edit Entry' : 'New Attendance Entry')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($entry) ? 'Edit Entry' : 'New Attendance Entry' }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ isset($entry) ? route('entries.update', $entry) : route('entries.store') }}">
            @csrf
            @if(isset($entry)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                    <select name="organization_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('organization_id') border-red-500 @enderror">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ old('organization_id', $entry->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device</label>
                    <select name="device_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— None —</option>
                        @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ old('device_id', $entry->device_id ?? '') == $device->id ? 'selected' : '' }}>
                            {{ $device->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                    <select name="emp_id" id="emp_id_select" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emp_id') border-red-500 @enderror">
                        <option value="">Select employee</option>
                        @foreach($employees as $emp)
                        @php $empVal = $emp->badge_number ?: (string)$emp->mdb_user_id @endphp
                        <option value="{{ $empVal }}" {{ old('emp_id', $entry->emp_id ?? '') == $empVal ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $empVal }})
                        </option>
                        @endforeach
                    </select>
                    @error('emp_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Real Employee ID</label>
                    <input type="text" name="real_emp_id" value="{{ old('real_emp_id', $entry->real_emp_id ?? '') }}" maxlength="50"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Time *</label>
                    <input type="datetime-local" name="check_time"
                        value="{{ old('check_time', isset($entry) ? $entry->check_time->format('Y-m-d\TH:i') : '') }}" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_time') border-red-500 @enderror">
                    @error('check_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— None —</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id', $entry->branch_id ?? '') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}{{ $b->location ? ' — '.$b->location : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device Name (override)</label>
                    <input type="text" name="device_name" value="{{ old('device_name', $entry->device_name ?? '') }}" maxlength="255"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Only used when no device is selected above.</p>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t">
                <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 font-medium text-sm">
                    {{ isset($entry) ? 'Update Entry' : 'Create Entry' }}
                </button>
                <a href="{{ route('entries.index') }}" class="text-gray-500 text-sm hover:underline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
