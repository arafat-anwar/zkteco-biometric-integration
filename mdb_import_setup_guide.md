# Guide: Fixing MDB Import "Could Not Find Driver" Error

If you are setting up the ZKTeco Biometric Integration on a new laptop and encounter the error `Failed to connect to MDB: could not find driver`, follow these steps to resolve it.

## 1. Enable Required PHP Extensions
The import command requires `pdo_odbc` for the primary connection and `pdo_sqlite` for the fallback mechanism.

1. Open your `php.ini` file (In Laragon: `Menu > PHP > php.ini`).
2. Search for the following lines and ensure they are **uncommented** (remove the `;` at the start):
   ```ini
   extension=pdo_odbc
   extension=odbc
   extension=pdo_sqlite
   extension=sqlite3
   ```
3. Save the file and restart your web server (Apache/Nginx).

## 2. Install Microsoft Access Database Engine (Required for Windows)
PHP needs a system-level driver to read `.mdb` files.

1. Download the **Microsoft Access Database Engine 2010 Redistributable** (or newer).
2. **Download Link**: [Microsoft Download Center](https://support.microsoft.com/en-us/download/details.aspx?id=13255)
3. **Important**: Download the version that matches your PHP architecture (usually **x64** for modern Laragon setups).
4. Install it and restart your laptop if prompted.

## 3. Verify Code Fixes (DSN Format)
Ensure the `ImportMdbCommand.php` file uses the correct DSN format. The driver name must be enclosed in single curly braces `{}`.

In `Modules\Pusher\app\Console\Commands\ImportMdbCommand.php`, check the `buildOdbcDsn` method:

```php
// CORRECT FORMAT
$dsn = "odbc:Driver={{$driver}};Dbq={$mdbPath};";

// INCORRECT FORMAT (Causes "driver not found")
// $dsn = "odbc:Driver={{{$driver}}};Dbq={$mdbPath};"; 
```

## 4. Fallback Option: MDB Tools
If you cannot install the ODBC driver, the system can fallback to using `mdb-export` if it is installed.

1. Install **mdbtools** for Windows.
2. Ensure `mdb-export.exe` is in your system PATH or installed at `C:\Program Files\mdbtools\`.

---

## Testing the Connection
Run the following command in your terminal to test the import:

```powershell
php artisan mdb:import
```

If successful, you should see:
`Trying driver: Microsoft Access Driver (*.mdb, *.accdb)`
`Import complete.`
