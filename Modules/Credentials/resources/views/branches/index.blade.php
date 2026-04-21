@extends('layouts.app')

@section('title', 'Branches')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Branches</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage office branches and their locations</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('mdb-import-modal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            &#8645; Import from MDB
        </button>
        <a href="{{ route('branches.create') }}"
            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            + New Branch
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
@endif

{{-- Organization filter --}}
<div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-4">
    <div class="flex items-center gap-3">
        <label class="text-sm font-medium text-gray-600">Organization:</label>
        <select id="org-filter" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Organizations</option>
            @foreach($organizations as $org)
            <option value="{{ $org->id }}">{{ $org->name }}</option>
            @endforeach
        </select>
        <button id="clear-filter" class="text-sm text-gray-500 hover:text-gray-800 underline">Clear</button>
    </div>
</div>

<div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <div class="p-6">
        <table id="branches-dt" class="w-full" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Organization</th>
                    <th>Status</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- MDB Import Modal --}}
@include('credentials::branches._mdb_import_modal')
@endsection

@push('scripts')
<script>
$(function () {
    var orgFilter = document.getElementById('org-filter');

    var table = $('#branches-dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("branches.data") }}',
            type: 'GET',
            data: function (d) { d.organization_id = orgFilter.value; },
        },
        columns: [
            { data: 'name' },
            { data: 'location_text', searchable: true,  orderable: true  },
            { data: 'org_name',      searchable: false, orderable: false },
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
            emptyTable:  'No branches found.',
            zeroRecords: 'No matching branches found.',
            search:      '',
            searchPlaceholder: 'Search branches…',
            lengthMenu:  'Show _MENU_',
        },
    });

    orgFilter.addEventListener('change', function () { table.ajax.reload(); });
    document.getElementById('clear-filter').addEventListener('click', function () {
        orgFilter.value = '';
        table.ajax.reload();
    });
});
</script>
@endpush
