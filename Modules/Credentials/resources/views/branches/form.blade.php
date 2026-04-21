@extends('layouts.app')

@section('title', isset($branch) ? 'Edit Branch' : 'New Branch')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($branch) ? 'Edit Branch' : 'New Branch' }}</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ isset($branch) ? route('branches.update', $branch) : route('branches.store') }}">
            @csrf
            @if(isset($branch)) @method('PUT') @endif

            @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded text-sm">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                    <input type="text" name="name" value="{{ old('name', $branch->name ?? '') }}" required maxlength="255"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                    <select name="organization_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('organization_id') border-red-500 @enderror">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ old('organization_id', $branch->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="{{ old('location', $branch->location ?? '') }}" maxlength="255"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center pt-4">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }} class="mr-2">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t">
                <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 font-medium text-sm">
                    {{ isset($branch) ? 'Update Branch' : 'Create Branch' }}
                </button>
                <a href="{{ route('branches.index') }}"
                    class="bg-gray-100 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-200 font-medium text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
