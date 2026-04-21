@extends('layouts.app')

@section('title', 'Attendance Entries')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Attendance Entries</h1>
        <p class="text-sm text-gray-500 mt-0.5">View and manage all check-in / check-out records</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('mdb-import-modal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            &#8645; Import from MDB
        </button>
        <a href="{{ route('entries.create') }}"
            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            + New Entry
        </a>
        <a href="{{ route('receiver.logs') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium ml-1">
            Push Logs →
        </a>
    </div>
</div>

{{-- Filters panel --}}
<div class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 mb-4">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Organization</label>
            <select id="f-org" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All</option>
                @foreach($organizations as $org)
                <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Device</label>
            <select id="f-device" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All</option>
                @foreach($devices as $device)
                <option value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee ID</label>
            <input id="f-emp" type="text" placeholder="e.g. EMP001"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-36">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">From Date</label>
            <input id="f-from" type="date"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">To Date</label>
            <input id="f-to" type="date"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-end gap-2 pb-0.5">
            <button id="apply-filters"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                Apply
            </button>
            <button id="clear-filters"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                Clear
            </button>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <div class="p-6">
        <table id="entries-dt" class="w-full" style="width:100%">
            <thead>
                <tr>
                    <th>Emp ID</th>
                    <th>Real Emp ID</th>
                    <th>Check Time</th>
                    <th>Organization</th>
                    <th>Device</th>
                    <th>Branch</th>
                    <th class="no-sort">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- MDB Import Modal --}}
@include('receiver::entries._mdb_import_modal')
@endsection

@push('scripts')
<script>
$(function () {
    var fOrg    = document.getElementById('f-org');
    var fDevice = document.getElementById('f-device');
    var fEmp    = document.getElementById('f-emp');
    var fFrom   = document.getElementById('f-from');
    var fTo     = document.getElementById('f-to');

    var table = $('#entries-dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("entries.data") }}',
            type: 'GET',
            data: function (d) {
                d.organization_id = fOrg.value;
                d.device_id       = fDevice.value;
                d.emp_id          = fEmp.value;
                d.from            = fFrom.value;
                d.to              = fTo.value;
            },
        },
        columns: [
            { data: 'emp_id' },
            { data: 'real_emp_id', defaultContent: '—' },
            { data: 'check_time' },
            { data: 'org_name',   searchable: false, orderable: false },
            { data: 'device_col', searchable: false, orderable: false },
            { data: 'branch_col', searchable: false, orderable: false },
            { data: 'actions',    searchable: false, orderable: false },
        ],
        order: [[2, 'desc']],
        pageLength: 50,
        lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
        dom: '<"dt-top-bar"<"dt-top-left"lB>f>rt<"dt-bot-bar"ip>',
        buttons: [
            { extend: 'csv',   text: '↓ CSV',   className: 'buttons-csv'   },
            { extend: 'excel', text: '↓ Excel', className: 'buttons-excel' },
            { extend: 'print', text: '⎙ Print', className: 'buttons-print' },
        ],
        language: {
            processing:  '<span class="text-blue-600 font-medium">⟳ Loading…</span>',
            emptyTable:  'No entries found.',
            zeroRecords: 'No matching entries found.',
            search:      '',
            searchPlaceholder: 'Quick search…',
            lengthMenu:  'Show _MENU_',
        },
    });

    document.getElementById('apply-filters').addEventListener('click', function () {
        table.ajax.reload();
    });
    document.getElementById('clear-filters').addEventListener('click', function () {
        fOrg.value = ''; fDevice.value = ''; fEmp.value = '';
        fFrom.value = ''; fTo.value = '';
        table.ajax.reload();
    });
});
</script>
@endpush
