<?php

namespace Modules\Pusher\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Branch;
use Modules\Credentials\Models\Device;
use Modules\Credentials\Models\Employee;
use Modules\Receiver\Models\AttendanceEntry;
use Modules\Receiver\Models\PushLog;

class ImportMdbCommand extends Command
{
    protected $signature = 'mdb:import
        {--mdb= : Path to the .mdb file (default: public/att2000.mdb)}
        {--org= : Organization ID to link imported data to}
        {--skip-devices : Skip importing devices from Machines table}
        {--skip-employees : Skip importing employees from USERINFO table}
        {--skip-branches : Skip importing branches from DEPARTMENTS table}
        {--skip-transactions : Skip importing transactions from CHECKINOUT table}
        {--from-date= : Filter transactions from this date (Y-m-d)}
        {--to-date= : Filter transactions up to this date (Y-m-d)}';

    protected $description = 'Import Devices, Employees & Transactions from a ZKTeco MDB file';

    private \PDO $pdo;

    public function handle(): int
    {
        $mdbPath = $this->option('mdb') ?? public_path('att2000.mdb');

        if (!file_exists($mdbPath)) {
            $this->error("MDB file not found: {$mdbPath}");
            return self::FAILURE;
        }

        $this->info("Connecting to MDB: {$mdbPath}");

        try {
            $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq={$mdbPath};";
            $this->pdo = new \PDO($dsn, '', '');
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $e) {
            $this->error("Failed to connect to MDB: " . $e->getMessage());
            return self::FAILURE;
        }

        $organization = $this->resolveOrganization();
        if (!$organization) {
            return self::FAILURE;
        }

        $this->info("Using organization: [{$organization->id}] {$organization->name}");
        $this->newLine();

        if (!$this->option('skip-devices')) {
            $this->importDevices($organization);
        }

        if (!$this->option('skip-employees')) {
            $this->importEmployees($organization);
        }

        if (!$this->option('skip-branches')) {
            $this->importBranches($organization);
        }

        if (!$this->option('skip-transactions')) {
            $this->importTransactions($organization);
        }

        $this->newLine();
        $this->info('Import complete.');

        return self::SUCCESS;
    }

    private function resolveOrganization(): ?Organization
    {
        if ($orgId = $this->option('org')) {
            $org = Organization::find($orgId);
            if (!$org) {
                $this->error("Organization ID {$orgId} not found.");
                return null;
            }
            return $org;
        }

        $orgs = Organization::where('is_active', true)->get();

        if ($orgs->isEmpty()) {
            $this->warn('No organizations found. Creating a default one...');
            return Organization::create([
                'name'      => 'Default Organization',
                'slug'      => 'default',
                'is_active' => true,
            ]);
        }

        if ($orgs->count() === 1) {
            return $orgs->first();
        }

        $choices = $orgs->map(fn($o) => "[{$o->id}] {$o->name}")->toArray();
        $choice  = $this->choice('Select organization to link imported data to:', $choices);
        $id      = (int) str($choice)->match('/\[(\d+)\]/');

        return $orgs->firstWhere('id', $id);
    }

    private function importDevices(Organization $organization): void
    {
        $this->info('--- Importing Devices (Machines table) ---');

        try {
            $stmt = $this->pdo->query("SELECT * FROM Machines WHERE Enabled = True");
            $machines = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->error("Failed to read Machines table: " . $e->getMessage());
            return;
        }

        if (empty($machines)) {
            $this->line('  No machines found.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($machines as $machine) {
            $sn = trim($machine['sn'] ?? '');

            $existing = Device::where('organization_id', $organization->id)
                ->where('serial_number', $sn)
                ->exists();

            if ($existing) {
                $this->line("  Skipped (already exists): SN={$sn}");
                $skipped++;
                continue;
            }

            $connectionType = ((int) $machine['ConnectType']) === 1 ? 'tcp' : 'odbc';

            Device::create([
                'organization_id' => $organization->id,
                'name'            => $machine['MachineAlias'] ?? "Device {$machine['ID']}",
                'ip_address'      => $machine['IP'] ?? null,
                'port'            => (int) ($machine['Port'] ?? 4370),
                'serial_number'   => $sn ?: null,
                'model'           => $machine['ProductType'] ?? null,
                'connection_type' => $connectionType,
                'is_active'       => true,
            ]);

            $this->line("  Created: [{$machine['ID']}] {$machine['MachineAlias']} ({$machine['IP']})");
            $created++;
        }

        $this->info("  Devices: {$created} created, {$skipped} skipped.");
    }

    private function importEmployees(Organization $organization): void
    {
        $this->info('--- Importing Employees (USERINFO table) ---');

        try {
            $stmt = $this->pdo->query("SELECT USERID, Badgenumber, Name, CardNo, DEFAULTDEPTID, privilege FROM USERINFO ORDER BY USERID");
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->error("Failed to read USERINFO table: " . $e->getMessage());
            return;
        }

        if (empty($users)) {
            $this->line('  No employees found.');
            return;
        }

        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                $userId = (int) $user['USERID'];
                $name   = trim($user['Name'] ?? '');

                if (empty($name)) {
                    $name = "Employee {$userId}";
                }

                $result = Employee::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'mdb_user_id'     => $userId,
                    ],
                    [
                        'badge_number' => trim($user['Badgenumber'] ?? '') ?: null,
                        'name'         => $name,
                        'card_no'      => trim($user['CardNo'] ?? '') ?: null,
                        'department_id' => (int) ($user['DEFAULTDEPTID'] ?? 0) ?: null,
                        'privilege'    => (int) ($user['privilege'] ?? 0),
                        'is_active'    => true,
                    ]
                );

                if ($result->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed importing employees: " . $e->getMessage());
            return;
        }

        $this->info("  Employees: {$created} created, {$updated} updated.");
    }

    private function importBranches(Organization $organization): void
    {
        $this->info('--- Importing Branches (DEPARTMENTS table) ---');

        try {
            $stmt  = $this->pdo->query("SELECT DEPTID, DEPTNAME FROM DEPARTMENTS ORDER BY DEPTID");
            $depts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->warn("  DEPARTMENTS table not found or unreadable: " . $e->getMessage());
            $this->line('  Skipping branches.');
            return;
        }

        if (empty($depts)) {
            $this->line('  No departments found.');
            return;
        }

        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($depts as $dept) {
                $name = trim($dept['DEPTNAME'] ?? '');

                if (empty($name)) {
                    continue;
                }

                $result = Branch::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name'            => $name,
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                if ($result->wasRecentlyCreated) {
                    $this->line("  Created: {$name}");
                    $created++;
                } else {
                    $updated++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed importing branches: " . $e->getMessage());
            return;
        }

        $this->info("  Branches: {$created} created, {$updated} updated.");
    }

    private function importTransactions(Organization $organization): void
    {
        $this->info('--- Importing Transactions (CHECKINOUT table) ---');

        // Build a map of device serial → device model
        $deviceMap = Device::where('organization_id', $organization->id)
            ->whereNotNull('serial_number')
            ->get()
            ->keyBy('serial_number');

        // Build employee map: mdb_user_id → badge_number
        $employeeMap = Employee::where('organization_id', $organization->id)
            ->get()
            ->keyBy('mdb_user_id');

        try {
            $sql        = "SELECT USERID, CHECKTIME, CHECKTYPE, sn FROM CHECKINOUT";
            $conditions = [];
            if ($from = $this->option('from-date')) {
                $conditions[] = "CHECKTIME >= #{$from}#";
            }
            if ($to = $this->option('to-date')) {
                $conditions[] = "CHECKTIME <= #{$to} 23:59:59#";
            }
            if ($conditions) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }
            $sql .= ' ORDER BY CHECKTIME ASC';

            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->error("Failed to read CHECKINOUT table: " . $e->getMessage());
            return;
        }

        if (empty($rows)) {
            $this->line('  No transactions found.');
            return;
        }

        $this->line("  Found " . count($rows) . " transaction(s).");

        $saved      = 0;
        $duplicates = 0;
        $errors     = 0;

        $deviceForLog = null;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $userId    = (int) $row['USERID'];
                $sn        = trim($row['sn'] ?? '');
                $checkTime = date('Y-m-d H:i:s', strtotime($row['CHECKTIME']));

                $device = $deviceMap[$sn] ?? null;

                /** @var Employee|null $employee */
                $employee = $employeeMap[$userId] ?? null;
                $empId    = $employee ? ($employee->badge_number ?? (string) $userId) : (string) $userId;

                $exists = AttendanceEntry::where([
                    'organization_id' => $organization->id,
                    'device_id'       => $device?->id,
                    'emp_id'          => $empId,
                    'check_time'      => $checkTime,
                ])->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                AttendanceEntry::create([
                    'organization_id' => $organization->id,
                    'device_id'       => $device?->id,
                    'emp_id'          => $empId,
                    'real_emp_id'     => (string) $userId,
                    'check_time'      => $checkTime,
                    'device_name'     => $device?->name ?? $sn,
                    'branch'          => $device?->location ?? null,
                ]);

                $saved++;
                if (!$deviceForLog && $device) {
                    $deviceForLog = $device;
                }
            }

            // Log the push operation
            PushLog::create([
                'organization_id'    => $organization->id,
                'device_id'          => $deviceForLog?->id,
                'records_pushed'     => count($rows),
                'records_saved'      => $saved,
                'duplicates_skipped' => $duplicates,
                'status'             => 'success',
                'message'            => 'Imported from MDB file',
                'pusher_ip'          => '127.0.0.1',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed importing transactions: " . $e->getMessage());
            return;
        }

        $this->info("  Transactions: {$saved} saved, {$duplicates} duplicates skipped.");
    }
}
