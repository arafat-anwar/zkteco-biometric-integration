@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Users</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage system access and roles</p>
    </div>
    <a href="{{ route('users.create') }}"
        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
        + New User
    </a>
</div>

<div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <div class="p-6">
        <table id="users-dt" class="w-full" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#users-dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '{{ route("users.data") }}', type: 'GET' },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'role_badge',   searchable: false, orderable: false },
            { data: 'status_badge', searchable: false, orderable: false },
            { data: 'created_at',   searchable: false },
            { data: 'actions',      searchable: false, orderable: false },
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"dt-top-bar"<"dt-top-left"lB>f>rt<"dt-bot-bar"ip>',
        buttons: [
            { extend: 'csv',   text: '↓ CSV',   className: 'buttons-csv'   },
            { extend: 'excel', text: '↓ Excel', className: 'buttons-excel' },
            { extend: 'print', text: '⎙ Print', className: 'buttons-print' },
        ],
        language: {
            processing:  '<span class="text-blue-600 font-medium">⟳ Loading…</span>',
            emptyTable:  'No users found.',
            zeroRecords: 'No matching users found.',
            search:      '',
            searchPlaceholder: 'Search users…',
            lengthMenu:  'Show _MENU_',
        },
    });
});
</script>
@endpush

