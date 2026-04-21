# ZKTeco Biometric Integration

An integration layer for ZKTeco biometric devices that extracts attendance and user data from Microsoft Access (MDB) files and directly from ZKTeco devices, and exposes an API for other systems to consume the data.

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

- Import an MDB file (example artisan command - adjust to your implementation):

  php artisan zkteco:import --file=public/att2000.mdb

- Poll a ZKTeco device (example):

  php artisan zkteco:poll --host=192.168.1.100 --port=4370

- API: Once the app is running, the REST endpoints described in `public/openapi.json` are available (default host `http://localhost:8000`). Use Swagger UI or tools like `curl` / Postman to explore.

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
- MDB import issues: ensure the PHP environment has an MDB-reading library available (e.g., `uodbc`, `mdbtools` on Linux, or use a PHP MDB reader package). On Windows, ODBC drivers can be used.
- Device connection issues: verify network connectivity and device IP/port, check firewall rules, and confirm device allows remote queries.

## Contributing
- Fork the repo, create a feature branch, add tests, and open a Pull Request against the `release` branch.

## License
Specify your project's license here (e.g., MIT).

