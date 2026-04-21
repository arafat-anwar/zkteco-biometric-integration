# ZKTeco Biometric Integration

<p align="center">
		<strong>Modular Laravel integration for ZKTeco devices — MDB import, device sync, and REST API.</strong>
</p>

<p align="center">
	<img src="https://github.com/arafat-anwar/zkteco-biometric-integration/actions/workflows/ci.yml/badge.svg" alt="build" />
	<img src="https://codecov.io/gh/arafat-anwar/zkteco-biometric-integration/branch/release/graph/badge.svg" alt="coverage" />
	<img src="https://img.shields.io/badge/license-MIT-green.svg" alt="license" />
</p>

> Documentation split across `docs/` — browse in-repo or view the published site:
>
> https://arafat-anwar.github.io/zkteco-biometric-integration/

<!-- Tab-like quick links -->
<p align="center">
	<a href="docs/overview.md">Overview</a> •
	<a href="docs/installation.md">Installation</a> •
	<a href="docs/usage.md">Usage</a> •
	<a href="docs/mdb-format.md">MDB Format</a> •
	<a href="docs/zkteco-devices.md">ZKTeco</a> •
	<a href="docs/api-documentation.md">API Docs</a> •
	<a href="docs/development.md">Development</a> •
	<a href="docs/contributing.md">Contributing</a> •
	<a href="docs/troubleshooting.md">Troubleshooting</a> •
	<a href="LICENSE">License</a>
</p>

---

## <a name="overview"></a>Overview

An integration layer for ZKTeco biometric devices that extracts attendance and user data from Microsoft Access (MDB) files and directly from ZKTeco devices, and exposes a clean REST API for HR/payroll systems.

### Highlights

- ✅ MDB import (sample: `public/att2000.mdb`)
- ✅ Device polling & sync (common port `4370`)
- ✅ OpenAPI / Swagger spec at `public/openapi.json`
- ✅ Modular Laravel structure under `Modules/`

---

## <a name="installation"></a>Installation

Follow these quick steps to get the project running locally.

```bash
git clone https://github.com/arafat-anwar/zkteco-biometric-integration.git
cd zkteco-biometric-integration
composer install
cp .env.example .env
# update .env DB settings
php artisan key:generate
php artisan migrate
npm install # optional (assets)
npm run build
php artisan serve
```

---

## <a name="usage"></a>Usage (Tab: Imports / Devices / API)

### Imports (UI)

Open the app in a browser, sign in as an admin/operator and go to **Imports** (or **Receiver → Import**). Use the Upload control to add an MDB file or use the server sample `att2000.mdb`. The UI runs the import job and shows progress and history.

### Devices (UI)

Navigate to **Devices** to add or edit ZKTeco devices (IP, port, name, credentials). Use the **Sync** action to pull logs; the UI shows last-sync and results.

### API (Tab)

The REST API is described by the OpenAPI spec at `/openapi.json`. Common endpoints:

- `GET /api/users` — list users
- `GET /api/attendances` — list attendance events
- `POST /api/import/mdb` — upload and import an MDB (if API import is enabled)

Authentication: include `Authorization: Bearer <TOKEN>` in requests.

Example: list attendances

```bash
curl -X GET "http://localhost:8000/api/attendances" \
	-H "Authorization: Bearer <TOKEN>" \
	-H "Accept: application/json"
```

Example: upload an MDB

```bash
curl -X POST "http://localhost:8000/api/import/mdb" \
	-H "Authorization: Bearer <TOKEN>" \
	-F "file=@/path/to/att2000.mdb"
```

---

## <a name="mdb-format"></a>How the MDB format works

The included `att2000.mdb` sample contains typical tables used by ZKTeco exports. Key columns you will find:

- `USERID` / `PIN` — unique user identifier
- `NAME` — user full name
- `VERIFYMODE` — verification method
- `CHECKTIME` / `ATT_TIME` — timestamp of attendance
- `CHECKTYPE` — in/out or event type

Importer behavior:

1. Open MDB and enumerate tables.
2. Read user table to create/update local users.
3. Read attendance/log table and insert attendance events.
4. Post-process: dedupe, normalize timestamps (UTC or configured TZ), and index.

If you must support alternate MDB schemas, add a column mapping in the importer configuration.

---

## <a name="zkteco-devices"></a>How ZKTeco devices work

ZKTeco devices typically accept network queries on TCP port `4370`. This project supports:

- Pulling users and logs via device protocol/SDK
- Export-to-MDB workflow (vendor tools) and subsequent import

Notes:

- Track `last_sync` per device to avoid duplicates.
- Implement retries and backoff for flaky networks.

---

## <a name="api-documentation"></a>API Documentation

The OpenAPI spec is at `public/openapi.json`. To view:

- Open `http://localhost:8000/openapi.json` with Swagger UI, or
- Load `public/openapi.json` at http://editor.swagger.io/.

The spec includes parameter descriptions, response schemas, and auth requirements. Use it to generate clients or a Postman collection.

---

## <a name="development"></a>Development notes

- Modules are under `Modules/` (e.g., `Modules/Receiver`, `Modules/Authentication`).
- Inspect `routes/` and `app/` inside each module for controllers and commands.
- Sample MDB for local testing: `public/att2000.mdb`.

---

## <a name="contributing"></a>Contributing

- Fork, add a feature branch, include tests, and open a PR against `release`.

---

## <a name="license"></a>License

This project is open source and licensed under the MIT License — see the `LICENSE` file for details.



## What it does
- Reads attendance and user records from an `att2000.mdb` sample database (Microsoft Access) and from ZKTeco devices over network.
- Normalizes records and exposes them via a REST API for easy consumption by HR or payroll systems.
- Supports importing MDB files, polling ZKTeco devices, and running one-off data pulls.

## Features
- MDB file import and parsing (sample `public/att2000.mdb`).
- Device polling using ZKTeco protocol for live retrieval.
- REST API with OpenAPI spec: see `public/openapi.json`.
- Laravel modules structure: modularized features under `Modules/`.

## Requirements
- PHP 8.1+ (follow project's composer.json)
- PHP ODBC extension (`php_odbc`) — required for reading MDB files and using ODBC drivers
- Composer
- A running database (MySQL, MariaDB, or SQLite for testing)
- Optional: Node.js and npm/yarn if running assets build

## Installation
1. Clone the repository:

	git clone https://github.com/arafat-anwar/zkteco-biometric-integration.git
	cd zkteco-biometric-integration

2. Install PHP dependencies:

	composer install

3. Copy and configure environment:

	cp .env.example .env
	# Update DB and other settings in .env

4. Generate application key and run migrations:

	php artisan key:generate
	php artisan migrate

5. (Optional) Install frontend deps and build assets:

	npm install
	npm run build

6. Start the application (local dev):

	php artisan serve

## Configuration
- Database connection: set `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`.
- MDB import path: by default the sample MDB is `public/att2000.mdb`. You can place other MDB files anywhere and point the import command to them.
- ZKTeco devices: configure device IP addresses and ports in the appropriate module config or `.env` (depending on deployment).

## Usage

- Import via web UI: No CLI commands are required for normal imports. Open the application in a browser, sign in with an admin or operator account, and navigate to the "Imports" or "Receiver" → "Import" page. Use the UI to upload an MDB file (for example `att2000.mdb`) or pick the sample file from the server. The UI validates the file, starts the import job, and shows progress and results in the import history view.

- Device management via web UI: Manage ZKTeco devices from the "Devices" page — add device IP, port (commonly `4370`), name, and credentials. Use the sync/poll action in the UI to trigger a device pull; the UI shows fetch progress and any import results. The system tracks last-sync timestamps to avoid duplicate reads.

- API: The app exposes a REST API described by the OpenAPI spec at `public/openapi.json` and served at `/openapi.json` when the app runs. You can view the spec with a Swagger UI, load it into http://editor.swagger.io/, or point any OpenAPI-compatible client at the URL.

	- Authentication: API endpoints require authentication (API tokens or other configured auth). Include an authorization header, for example `Authorization: Bearer <TOKEN>`.

	- Example: list attendances with curl

		curl -X GET "http://localhost:8000/api/attendances" \
			-H "Authorization: Bearer <TOKEN>" \
			-H "Accept: application/json"

	- Example: upload an MDB via API (if enabled)

		curl -X POST "http://localhost:8000/api/import/mdb" \
			-H "Authorization: Bearer <TOKEN>" \
			-F "file=@/path/to/att2000.mdb"

	- Query parameters: endpoints may support filters such as `since`, `until`, `user_id`, and pagination parameters. Consult the OpenAPI spec for full parameter lists and response schemas.

## How the MDB format works

The Microsoft Access database (`.mdb`) used by many ZKTeco device export tools (for example the Att2000 sample) typically contains attendance and user tables with columns such as:

- `USERID` / `PIN` — unique user identifier
- `NAME` — user full name
- `VERIFYMODE` — verification method
- `CHECKTIME` / `ATT_TIME` — timestamp of attendance
- `CHECKTYPE` — in/out or event type

The project includes an importer that:
- Opens the MDB file using an MDB-to-SQL reader library (or `odbc`/`pdo` depending on environment).
- Maps table columns to the application's user and attendance models.
- Normalizes timestamps to UTC (or configured timezone) and deduplicates records.

Sample workflow when importing an MDB file:
1. Open MDB and enumerate tables.
2. Read user table to create/update local users.
3. Read attendance/log table and insert attendance events.
4. Run post-processing: dedupe, enrich, and index for API reads.

If you need support for different MDB layouts, add a mapping configuration for column names to the importer.

## How ZKTeco devices work (overview)

ZKTeco biometric devices typically support a network protocol (TCP/UDP) to retrieve user and attendance data. There are two common approaches this project supports:

1. Pull from device using device SDK/protocol: connect to device IP and request user list and attendance logs.
2. Export to MDB (from vendor tools) and import the MDB.

Key notes:
- Devices often use port `4370` for communication.
- Authentication may be required depending on device model; configure credentials where applicable.
- Network reliability: implement retries and incremental reads (track last read index/time) to avoid duplicate fetching.

## API Documentation

An OpenAPI/Swagger specification is included at `public/openapi.json`. To view the API:

- Serve the app (php artisan serve) and open a Swagger UI pointed at `http://localhost:8000/openapi.json` (or load `public/openapi.json` into http://editor.swagger.io/).
- Example endpoints you can expect (adjust to actual implementation):
  - `GET /api/users` — list users
  - `GET /api/attendances` — list attendance events
  - `POST /api/import/mdb` — upload and import an MDB file

Include authentication (API tokens) where required by the project. Check `Modules/Authentication` for implementation details.

## Development notes
- Modules are located under `Modules/` (e.g., `Modules/Receiver`, `Modules/Authentication`). Review module `routes/` and `app/` folders for controllers and commands.
- Sample MDB: `public/att2000.mdb` — useful for local testing.
- OpenAPI spec: `public/openapi.json` — a single source of truth for API endpoints.

## Troubleshooting
- MDB import issues: ensure the PHP environment has an MDB-reading library available (e.g., `uodbc`, `mdbtools` on Linux, or use a PHP MDB reader package). On Windows, ODBC drivers can be used. Also ensure the `php_odbc` extension is enabled in your `php.ini` and the web server/PHP-FPM has been restarted.
- Device connection issues: verify network connectivity and device IP/port, check firewall rules, and confirm device allows remote queries.

### Enabling `php_odbc` (platform-specific)

If MDB import fails, make sure the PHP ODBC extension is installed and the system has an ODBC driver for Access (or `mdbtools` for Linux).

- Debian / Ubuntu:

```bash
sudo apt update
sudo apt install php-odbc unixodbc mdbtools
# If using a specific PHP version (replace 8.1 as needed):
sudo apt install php8.1-odbc
sudo systemctl restart apache2    # or php8.1-fpm
```

- RHEL / CentOS / Fedora:

```bash
sudo dnf install php-odbc unixODBC mdbtools
sudo systemctl restart httpd     # or php-fpm
```

- Windows:

1. Install the Microsoft Access Database Engine (ACE) / Microsoft Access ODBC drivers (Microsoft Access Database Engine Redistributable) so the OS can read `.mdb` files.
2. Edit your `php.ini` (used by the webserver/PHP) and enable the ODBC extension by adding or uncommenting one of the lines below (exact name varies by PHP build):

```
extension=odbc
; or
extension=php_odbc.dll
```

3. Restart IIS, Apache or PHP-FPM and verify the extension is loaded:

```bash
php -m | grep odbc
php -r "var_dump(extension_loaded('odbc'));"
```

- Verification: After enabling, run `php -m` or the PHP test above to confirm `odbc` appears in the module list.

### Useful links & resources

- Microsoft Access Database Engine (ACE) / ODBC drivers (Windows): https://www.microsoft.com/en-us/download/details.aspx?id=54920
- mdbtools (Linux toolset for reading MDB files): https://github.com/mdbtools/mdbtools
- unixODBC: http://www.unixodbc.org/

### Common ODBC errors & fixes

- "could not find driver" / "SQLSTATE[IM002]": The ODBC driver or `php_odbc` extension is not installed or enabled. Install the platform ODBC driver (ACE on Windows or unixODBC/mdbtools on Linux) and enable `php_odbc` in `php.ini`.
- "permission denied" when opening `.mdb`: Ensure the web server/PHP process has read permissions for the MDB file and the containing folder; avoid placing MDB in restricted system folders.
- "file format not recognized" or corrupt records: Verify the MDB is a supported format (older Access versions may need conversion). Try opening with Access or use `mdbtools` to inspect.
- Timezone/timestamp drift: Confirm PHP `date.timezone` is configured and the importer normalizes times to UTC or the configured TZ.
- Duplicate import results: Confirm `last_sync` is tracked per device and deduplication logic is enabled; clear import history only with caution.



## Contributing
- Fork the repo, create a feature branch, add tests, and open a Pull Request against the `release` branch.

## License
This project is open source and licensed under the MIT License — see the `LICENSE` file for details.

