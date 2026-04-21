<?php

namespace Modules\Pusher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Symfony\Component\Process\Process;

class MdbImportController extends Controller
{
    protected function authorizeManager(): void
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Unauthorized');
        }
    }

    // ----------------------------------------------------------------
    // Devices
    // ----------------------------------------------------------------

    public function importDevices(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_file'        => ['required', 'file', 'extensions:mdb,accdb'],
        ]);

        $mdbPath = $this->storeMdb($request->file('mdb_file'));

        [$success, $output, $error] = $this->runArtisan([
            '--skip-employees',
            '--skip-transactions',
        ], $request->organization_id, $mdbPath);

        $this->cleanMdb($mdbPath);

        if (!$success) {
            return back()->with('error', 'Import failed: ' . $this->extractError($output, $error));
        }

        return redirect()->route('devices.index')
            ->with('success', $this->extractSummary($output, 'Devices'));
    }

    // ----------------------------------------------------------------
    // Employees
    // ----------------------------------------------------------------

    public function importEmployees(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_file'        => ['required', 'file', 'extensions:mdb,accdb'],
        ]);

        $mdbPath = $this->storeMdb($request->file('mdb_file'));

        [$success, $output, $error] = $this->runArtisan([
            '--skip-devices',
            '--skip-transactions',
        ], $request->organization_id, $mdbPath);

        $this->cleanMdb($mdbPath);

        if (!$success) {
            return back()->with('error', 'Import failed: ' . $this->extractError($output, $error));
        }

        return redirect()->route('employees.index')
            ->with('success', $this->extractSummary($output, 'Employees'));
    }

    // ----------------------------------------------------------------
    // Branches
    // ----------------------------------------------------------------

    public function importBranches(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_file'        => ['required', 'file', 'extensions:mdb,accdb'],
        ]);

        $mdbPath = $this->storeMdb($request->file('mdb_file'));

        [$success, $output, $error] = $this->runArtisan([
            '--skip-devices',
            '--skip-employees',
            '--skip-transactions',
        ], $request->organization_id, $mdbPath);

        $this->cleanMdb($mdbPath);

        if (!$success) {
            return back()->with('error', 'Import failed: ' . $this->extractError($output, $error));
        }

        return redirect()->route('branches.index')
            ->with('success', $this->extractSummary($output, 'Branches'));
    }

    // ----------------------------------------------------------------
    // Attendance Entries
    // ----------------------------------------------------------------

    public function importEntries(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_file'        => ['required', 'file', 'extensions:mdb,accdb'],
            'from_date'       => ['nullable', 'date'],
            'to_date'         => ['nullable', 'date'],
        ]);

        $mdbPath = $this->storeMdb($request->file('mdb_file'));

        $extraArgs = ['--skip-devices', '--skip-employees'];

        if ($request->filled('from_date')) {
            $extraArgs[] = '--from-date=' . $request->from_date;
        }
        if ($request->filled('to_date')) {
            $extraArgs[] = '--to-date=' . $request->to_date;
        }

        [$success, $output, $error] = $this->runArtisan($extraArgs, $request->organization_id, $mdbPath);

        $this->cleanMdb($mdbPath);

        if (!$success) {
            return back()->with('error', 'Import failed: ' . $this->extractError($output, $error));
        }

        return redirect()->route('entries.index')
            ->with('success', $this->extractSummary($output, 'Transactions'));
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Run the mdb:import artisan command via CLI PHP (which has ODBC loaded).
     * Returns [success, stdout, stderr].
     */
    private function runArtisan(array $extraArgs, int $orgId, string $mdbPath): array
    {
        $phpBin  = $this->phpCliBinary();
        $artisan = base_path('artisan');

        $cmd = array_merge(
            [$phpBin, $artisan, 'mdb:import', '--no-interaction',
             '--org=' . $orgId,
             '--mdb=' . $mdbPath],
            $extraArgs
        );

        $process = new Process($cmd, base_path(), null, null, 120);
        $process->run();

        return [
            $process->isSuccessful(),
            $process->getOutput(),
            $process->getErrorOutput(),
        ];
    }

    /**
     * Return the path to the PHP CLI binary (php.exe), not php-cgi.exe.
     * The CLI binary has pdo_odbc loaded while php-cgi may not.
     */
    private function phpCliBinary(): string
    {
        $dir = dirname(PHP_BINARY);

        // Prefer php.exe over php-cgi.exe
        foreach (['php.exe', 'php'] as $bin) {
            $path = $dir . DIRECTORY_SEPARATOR . $bin;
            if (file_exists($path)) {
                return $path;
            }
        }

        return PHP_BINARY;
    }

    private function storeMdb(\Illuminate\Http\UploadedFile $file): string
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $name = 'mdb_import_' . uniqid() . '.mdb';
        $file->move($tmpDir, $name);

        return $tmpDir . DIRECTORY_SEPARATOR . $name;
    }

    private function cleanMdb(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Extract error text from process output or stderr.
     */
    private function extractError(string $stdout, string $stderr): string
    {
        foreach (explode("\n", $stdout) as $line) {
            $line = trim(strip_tags($line));
            if ($line && preg_match('/error|failed|exception/i', $line)) {
                return $line;
            }
        }

        return trim($stderr) ?: 'Unknown error. Check server logs.';
    }

    /**
     * Extract a short summary line from artisan output.
     */
    private function extractSummary(string $stdout, string $context): string
    {
        $lines = array_filter(
            array_map(fn($l) => trim(strip_tags($l)), explode("\n", $stdout)),
            fn($l) => str_contains(strtolower($l), strtolower($context))
        );

        return !empty($lines)
            ? implode(' | ', array_values($lines))
            : 'MDB import completed successfully.';
    }
}
