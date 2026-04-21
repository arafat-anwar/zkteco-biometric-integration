<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ZKTeco Biometric Integration')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* ======================================
           DataTables — Custom Stylish Theme
           ====================================== */
        .dataTables_wrapper { position: relative; font-size: 0.875rem; }

        /* Top / Bottom control bars */
        .dt-top-bar  { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.25rem; }
        .dt-top-left { display:flex; align-items:center; gap:0.625rem; flex-wrap:wrap; }
        .dt-bot-bar  { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-top:1rem; padding-top:0.875rem; border-top:1px solid #f1f5f9; }

        /* Table */
        table.dataTable { width:100% !important; border-collapse:collapse !important; }
        table.dataTable thead th,
        table.dataTable thead td {
            background-color:#f8fafc; color:#475569; font-size:0.7rem; font-weight:600;
            text-transform:uppercase; letter-spacing:0.07em; padding:0.875rem 1rem;
            border-bottom:2px solid #e2e8f0; white-space:nowrap;
        }
        table.dataTable thead th.sorting        { cursor:pointer; background-image:none !important; padding-right:1.5rem; position:relative; }
        table.dataTable thead th.sorting::after { content:'↕'; position:absolute; right:0.5rem; opacity:0.35; font-size:0.75rem; }
        table.dataTable thead th.sorting_asc    { background-color:#eff6ff; color:#2563eb; }
        table.dataTable thead th.sorting_asc::after  { content:'↑'; opacity:1; color:#2563eb; }
        table.dataTable thead th.sorting_desc   { background-color:#eff6ff; color:#2563eb; }
        table.dataTable thead th.sorting_desc::after { content:'↓'; opacity:1; color:#2563eb; }
        table.dataTable thead th:hover { background-color:#f1f5f9; }

        table.dataTable tbody td {
            padding:0.8rem 1rem; color:#374151; border-bottom:1px solid #f1f5f9; vertical-align:middle;
        }
        table.dataTable tbody tr:last-child td { border-bottom:none; }
        table.dataTable tbody tr:hover td { background-color:#f8faff; }

        /* Processing */
        .dataTables_processing {
            position:absolute !important; top:50% !important; left:50% !important;
            transform:translate(-50%,-50%) !important; z-index:100;
            background:rgba(255,255,255,0.97) !important; border:1px solid #e2e8f0 !important;
            border-radius:0.75rem !important; padding:0.875rem 1.5rem !important;
            font-size:0.875rem !important; color:#2563eb !important; font-weight:500 !important;
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.08) !important; min-width:130px; text-align:center;
        }

        /* Controls */
        .dataTables_length label,
        .dataTables_filter label { display:flex; align-items:center; gap:0.5rem; color:#6b7280; font-weight:500; }
        .dataTables_length select,
        .dataTables_filter input[type=search] {
            border:1px solid #d1d5db; border-radius:0.5rem; padding:0.4rem 0.75rem;
            font-size:0.8rem; color:#374151; background:white; outline:none;
            transition:border-color 0.15s, box-shadow 0.15s;
        }
        .dataTables_filter input[type=search] { min-width:200px; }
        .dataTables_length select:focus,
        .dataTables_filter input[type=search]:focus {
            border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.12);
        }
        .dataTables_info { font-size:0.8rem; color:#6b7280; padding-top:0.125rem; }

        /* Pagination */
        .dataTables_paginate { display:flex; align-items:center; flex-wrap:wrap; gap:0.25rem; }
        .dataTables_paginate span { display:flex; gap:0.25rem; }
        .dataTables_paginate .paginate_button {
            display:inline-flex; align-items:center; justify-content:center;
            min-width:2rem; height:2rem; padding:0 0.5rem; border-radius:0.5rem;
            font-size:0.8rem; cursor:pointer; color:#374151;
            border:1px solid transparent !important; background:transparent !important;
            box-shadow:none !important; transition:all 0.15s; line-height:1;
        }
        .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background-color:#eff6ff !important; color:#2563eb !important; border-color:#bfdbfe !important;
        }
        .dataTables_paginate .paginate_button.current,
        .dataTables_paginate .paginate_button.current:hover {
            background-color:#2563eb !important; color:#fff !important;
            border-color:#2563eb !important; font-weight:600;
        }
        .dataTables_paginate .paginate_button.disabled,
        .dataTables_paginate .paginate_button.disabled:hover {
            color:#9ca3af !important; cursor:not-allowed; opacity:0.5;
        }

        /* Buttons extension */
        .dt-buttons { display:flex; gap:0.375rem; flex-wrap:wrap; }
        .dt-button {
            display:inline-flex !important; align-items:center; padding:0.38rem 0.75rem !important;
            border-radius:0.5rem !important; font-size:0.78rem !important; font-weight:500 !important;
            cursor:pointer; transition:all 0.15s; border:1px solid #d1d5db !important;
            background:white !important; color:#374151 !important;
            box-shadow:none !important; text-shadow:none !important;
        }
        .dt-button:hover                 { background-color:#f9fafb !important; border-color:#9ca3af !important; }
        .dt-button.buttons-csv           { color:#15803d !important; border-color:#86efac !important; background:#f0fdf4 !important; }
        .dt-button.buttons-csv:hover     { background:#dcfce7 !important; }
        .dt-button.buttons-excel         { color:#15803d !important; border-color:#86efac !important; background:#f0fdf4 !important; }
        .dt-button.buttons-excel:hover   { background:#dcfce7 !important; }
        .dt-button.buttons-print         { color:#6d28d9 !important; border-color:#c4b5fd !important; background:#f5f3ff !important; }
        .dt-button.buttons-print:hover   { background:#ede9fe !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">

{{-- Navbar --}}
<nav class="bg-blue-700 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="font-bold text-lg tracking-wide">
                    &#x25CF; ZKTeco Integration
                </a>
                @auth
                <a href="{{ route('pusher.index') }}" class="hover:text-blue-200 text-sm">Pusher</a>
                <a href="{{ route('entries.index') }}" class="hover:text-blue-200 text-sm">Entries</a>
                @if(auth()->user()->isManager())
                <a href="{{ route('organizations.index') }}" class="hover:text-blue-200 text-sm">Organizations</a>
                <a href="{{ route('devices.index') }}" class="hover:text-blue-200 text-sm">Devices</a>
                <a href="{{ route('employees.index') }}" class="hover:text-blue-200 text-sm">Employees</a>
                <a href="{{ route('branches.index') }}" class="hover:text-blue-200 text-sm">Branches</a>
                @endif
                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="hover:text-blue-200 text-sm">Users</a>
                @endif
                <a href="/api-docs" class="hover:text-blue-200 text-sm">API Docs</a>
                @endauth
            </div>
            @auth
            <div class="flex items-center space-x-4">
                <span class="text-sm text-blue-200">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm bg-blue-800 hover:bg-blue-900 px-3 py-1 rounded">
                        Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
        {{ session('error') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
        {{ session('warning') }}
    </div>
    @endif

    @yield('content')
</div>

{{-- jQuery + DataTables JS (global, loaded before page scripts) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
@stack('scripts')
</body>
</html>
