@extends('layouts.app')

@section('title', 'Organizations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Organizations</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage tenants and their settings</p>
    </div>
    <a href="{{ route('organizations.create') }}"
        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
        + New Organization
    </a>
</div>

<div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <div class="p-6">
        <table id="orgs-dt" class="w-full" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Devices</th>
                    <th>Status</th>
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
    $('#orgs-dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: '{{ route("organizations.data") }}', type: 'GET' },
        columns: [
            { data: 'name' },
            { data: 'slug' },
            { data: 'email',         searchable: true,  orderable: true },
            { data: 'phone',         searchable: true,  orderable: true },
            { data: 'devices_badge', searchable: false, orderable: false },
            { data: 'status_badge',  searchable: false, orderable: false },
            { data: 'actions',       searchable: false, orderable: false },
        ],
        order: [[0, 'asc']],
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
            emptyTable:  'No organizations found.',
            zeroRecords: 'No matching organizations found.',
            search:      '',
            searchPlaceholder: 'Search organizations…',
            lengthMenu:  'Show _MENU_',
        },
    });
});
</script>
@endpush
