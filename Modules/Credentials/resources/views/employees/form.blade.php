@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Employee' : 'New Employee')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($employee) ? 'Edit Employee' : 'New Employee' }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}">
            @csrf
            @if(isset($employee)) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                    <select name="organization_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('organization_id') border-red-500 @enderror">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ old('organization_id', $employee->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Device User ID (MDB) *</label>
                    <input type="number" name="mdb_user_id" value="{{ old('mdb_user_id', $employee->mdb_user_id ?? '') }}" required min="1"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mdb_user_id') border-red-500 @enderror">
                    @error('mdb_user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge Number</label>
                    <input type="text" name="badge_number" value="{{ old('badge_number', $employee->badge_number ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('badge_number') border-red-500 @enderror">
                    @error('badge_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input type="text" name="card_no" value="{{ old('card_no', $employee->card_no ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department ID</label>
                    <input type="number" name="department_id" value="{{ old('department_id', $employee->department_id ?? '') }}" min="0"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Privilege *</label>
                    <select name="privilege" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0" {{ old('privilege', $employee->privilege ?? 0) == 0 ? 'selected' : '' }}>Normal</option>
                        <option value="1" {{ old('privilege', $employee->privilege ?? 0) == 1 ? 'selected' : '' }}>Enroller</option>
                        <option value="2" {{ old('privilege', $employee->privilege ?? 0) == 2 ? 'selected' : '' }}>Manager</option>
                        <option value="3" {{ old('privilege', $employee->privilege ?? 0) == 3 ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('privilege') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }} class="mr-2">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t">
                <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 font-medium text-sm">
                    {{ isset($employee) ? 'Update Employee' : 'Create Employee' }}
                </button>
                <a href="{{ route('employees.index') }}" class="text-gray-500 text-sm hover:underline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
