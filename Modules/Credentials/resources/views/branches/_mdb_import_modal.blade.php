<div id="mdb-import-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Import Branches from MDB</h3>
            <button onclick="document.getElementById('mdb-import-modal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('mdb.import.branches') }}" enctype="multipart/form-data">
            @csrf
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization *</label>
                    <select name="organization_id" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select organization</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MDB File *</label>
                    <input type="file" name="mdb_file" accept=".mdb,.accdb" required
                        class="w-full text-sm text-gray-600 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Reads the <strong>DEPARTMENTS</strong> table from the ZKTeco MDB database. Existing branches (matched by name) will be updated.</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-4 py-3 border-t bg-gray-50 rounded-b-lg">
                <button type="button" onclick="document.getElementById('mdb-import-modal').classList.add('hidden')"
                    class="text-sm text-gray-600 hover:underline">Cancel</button>
                <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Import Branches
                </button>
            </div>
        </form>
    </div>
</div>
