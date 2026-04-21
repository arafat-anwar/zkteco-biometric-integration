# ZKTeco Biometric Integration

Modular Laravel integration for ZKTeco biometric devices. Import attendance from Microsoft Access (`.mdb`) exports or sync directly from devices and expose data via a documented REST API.

[![Build](https://github.com/arafat-anwar/zkteco-biometric-integration/actions/workflows/ci.yml/badge.svg)](https://github.com/arafat-anwar/zkteco-biometric-integration/actions) [![License: MIT](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net) [![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)

---

## Contents

- [How it works](#how-it-works)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Usage](#usage)
- [MDB Format](#mdb-format)
- [ZKTeco Devices](#zkteco-devices)
- [API Documentation](#api-documentation)
- [Development](#development)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

---

## How it works

ZKTeco devices record fingerprint, face, or card-based attendance. This application pulls that data in two ways — from exported database files or directly from the device — normalizes it, and serves it through a REST API.

```
ZKTeco Device ─── TCP port 4370 ───┐
                                    ├──► Laravel App ──► REST API ──► HR / Payroll
ZKTeco Software ── att2000.mdb ─────┘          │
                                               └──► Web UI  (import · sync · browse)
```

**Core modules:**

| Module | Responsibility |
|---|---|
| `Receiver` | MDB import engine and device sync scheduler |
| `API` | REST endpoints, authentication, OpenAPI spec |
| `Authentication` | Token issuance and guard management |
| `Credentials` | Encrypted device credential storage |
| `Pusher` | Real-time event broadcasting |

---

## Prerequisites

**Runtime requirements**

| Requirement | Minimum | Notes |
|---|---|---|
| PHP | 8.1 | `php -v` to verify |
| Composer | 2.x | `composer -V` to verify |
| Database | MySQL 8 / MariaDB 10.4 / PostgreSQL / SQLite | Set in `.env` |
| Node.js + npm | 18 | For compiling frontend assets (optional) |

**Required PHP extensions**

| Extension | Purpose |
|---|---|
| `php_odbc` | Reading `.mdb` (Microsoft Access) files |
| `pdo` | Database connectivity |
| `mbstring` | Multi-byte string handling |
| `openssl` | Encryption and token signing |

**ODBC drivers (for MDB reading)**

| Platform | What to install |
|---|---|
| Ubuntu / Debian | `unixodbc`, `mdbtools` |
| RHEL / Fedora | `unixODBC`, `mdbtools` |
| Windows | Microsoft Access Database Engine Redistributable |
| macOS | `unixodbc` via Homebrew |

**Network**

- PHP server must be able to reach the ZKTeco device on **TCP port 4370** (for live sync).

---

## Installation

**1. Clone the repository**

```bash
git clone https://github.com/arafat-anwar/zkteco-biometric-integration.git
cd zkteco-biometric-integration
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Set up environment**

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and fill in your database details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zkteco
DB_USERNAME=root
DB_PASSWORD=secret
```

**4. Run database migrations**

```bash
php artisan migrate
```

**5. Build frontend assets** *(optional)*

```bash
npm install && npm run build
```

**6. Start the application**

```bash
php artisan serve
# Listening at http://localhost:8000
```

## ZKTeco Biometric Integration

Modular Laravel integration for ZKTeco biometric devices. Import attendance from Microsoft Access (`.mdb`) exports or sync directly from devices and expose the data through a documented REST API.

### Quick links

- Overview
- Installation
- Usage (UI + API)
- MDB format
- Device sync
- API docs
- Development
- Troubleshooting

---

### How it works

ZKTeco devices and their vendor software produce attendance records. This app ingests those records either by reading exported `.mdb` files or by connecting to devices over TCP (default port `4370`). Imported data is normalized and stored in your application database for use via the web UI or REST API.

High level flow:

```
Device (TCP:4370)  --->  App (sync)  ---> Database  ---> API / UI
MDB file (att2000.mdb) ---> Importer ---> Database  ---> API / UI
```

### What it includes

- MDB import engine (sample: `public/att2000.mdb`)
- Device sync module (connects on TCP port 4370)
- REST API with OpenAPI spec (`public/openapi.json`)
- Admin UI for imports, devices, and record browsing

---

### Prerequisites

- PHP 8.1+
- Composer
- A database (MySQL/MariaDB/Postgres/SQLite)
- `php_odbc` extension (required for reading `.mdb` via ODBC)
- Node.js + npm (optional, for frontend build)

Install platform ODBC drivers when using `.mdb` files (e.g. `mdbtools` + `unixodbc` on Linux or Microsoft ACE on Windows).

---

### Installation (short)

```bash
git clone https://github.com/arafat-anwar/zkteco-biometric-integration.git
cd zkteco-biometric-integration
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build # optional
php artisan serve
```

---

### Usage (UI)

- Go to `http://localhost:8000`
- Use **Imports** to upload an `.mdb` file or use `public/att2000.mdb`
- Use **Devices** to register a device and **Sync** to pull logs
- Browse users and attendance under **Records**

### Usage (API)

- Open `http://localhost:8000/openapi.json` with Swagger UI or Postman
- All endpoints require `Authorization: Bearer <TOKEN>`

Common endpoints:

```
GET  /api/users
GET  /api/attendances
POST /api/import/mdb  (multipart file upload)
```

Example:

```bash
curl -X GET "http://localhost:8000/api/attendances" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

### MDB format (overview)

- Users: `USERID`, `PIN`, `NAME`, `VERIFYMODE`
- Attendance: `USERID`, `CHECKTIME`, `CHECKTYPE`

Importer pipeline:

1. Open `.mdb` using ODBC
2. Map tables/columns to models
3. Insert users and attendance events
4. Deduplicate and normalize timestamps (UTC)

---

### Device sync notes

- Devices commonly listen on TCP port `4370`
- The app tracks `last_sync` per device to fetch only new records
- Retries and backoff handle flaky networks

---

### API docs

View the OpenAPI spec at `public/openapi.json` or import it into Postman / Swagger Editor.

---

### Development

- Modules under `Modules/` (Receiver, API, Authentication, Credentials, Pusher)
- Sample MDB: `public/att2000.mdb`
- Tests: `php artisan test`

---

### Troubleshooting (common)

- If MDB import fails, ensure `php_odbc` is enabled and platform ODBC drivers are installed.
- Check network/firewall for device port `4370`.
- Verify file permissions for `.mdb` files.

---

### Contributing

Fork → feature branch → tests → PR against `release`.

---

License: MIT — see `LICENSE`.

  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Upload an MDB file:**
```bash
curl -X POST "http://localhost:8000/api/import/mdb" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/att2000.mdb"
```

</details>

---

<details>
<summary><h2>📁 MDB Format</h2></summary>

ZKTeco's own management software (e.g. Att2000) can export attendance data to a Microsoft Access database file (`.mdb`). The included `public/att2000.mdb` is a working sample.

### Table structure

| Table | Key Columns | Purpose |
|---|---|---|
| Users | `USERID`, `PIN`, `NAME`, `VERIFYMODE` | Employee records |
| Attendance | `USERID`, `CHECKTIME`, `CHECKTYPE` | Punch-in/out events |

### Column reference

| Column | Type | Description |
|---|---|---|
| `USERID` / `PIN` | int | Unique employee identifier |
| `NAME` | string | Full name |
| `VERIFYMODE` | int | Verification method (fingerprint, card, PIN…) |
| `CHECKTIME` / `ATT_TIME` | datetime | Timestamp of event |
| `CHECKTYPE` | int | Event type (check-in, check-out, overtime…) |

### Importer workflow

```
Open .mdb via ODBC
     │
     ├── Read user table ──► Create / update local users
     │
     ├── Read attendance table ──► Insert attendance events
     │
     └── Post-process: dedupe, normalize timestamps (UTC), index
```

> **Custom schemas**: If your MDB uses different column names, add a column mapping in the importer configuration file.

</details>

---

<details>
<summary><h2>🔌 ZKTeco Devices</h2></summary>

ZKTeco biometric devices (fingerprint scanners, face recognition terminals, etc.) expose a TCP API on port **4370** for direct data retrieval.

### Supported workflows

| Workflow | How it works |
|---|---|
| **Direct polling** | App connects to device IP:4370 and pulls users + attendance logs |
| **MDB import** | Use ZKTeco vendor software to export `.mdb`, then import via UI or API |

### Device setup in the app

1. Add the device in **Devices → New Device**
2. Enter: **Name**, **IP address**, **Port** (default `4370`), **Serial number** (optional), **Password** (if set on device)
3. Click **Test Connection** to verify
4. Use **Sync** to pull the latest logs

### Important notes

- The app tracks `last_sync` per device to fetch only new records
- Retries with exponential backoff handle flaky network connections
- Device passwords are stored encrypted in the database
- Make sure the device's network settings allow external connections (check device admin panel under **Comm → IP & DNS**)

</details>

---

<details>
<summary><h2>📚 API Documentation</h2></summary>

The full API is described by an **OpenAPI 3.0** specification stored at `public/openapi.json`.

### Viewing the spec

**Option 1 — Swagger UI (recommended):**
1. Start the app (`php artisan serve`)
2. Open [Swagger Editor](https://editor.swagger.io/) or any Swagger UI instance
3. Load `http://localhost:8000/openapi.json`

**Option 2 — Swagger Editor online:**
1. Open [https://editor.swagger.io/](https://editor.swagger.io/)
2. Go to **File → Import URL** and paste `http://localhost:8000/openapi.json`

**Option 3 — Postman:**
1. In Postman, choose **Import → Link**
2. Paste `http://localhost:8000/openapi.json`
3. A full collection is generated automatically

### Spec contents

The spec documents:
- All available endpoints with parameters and request bodies
- Response schemas and status codes
- Authentication requirements (Bearer token)
- Example request/response payloads

### Generating API clients

Use [OpenAPI Generator](https://openapi-generator.tech/) to generate a typed client in any language:

```bash
# Example: generate a PHP client
openapi-generator-cli generate \
  -i http://localhost:8000/openapi.json \
  -g php \
  -o ./generated/php-client
```

> Check `Modules/Authentication` for token issuance and management.

</details>

---

<details>
<summary><h2>🏗️ Development</h2></summary>

### Project structure

```
zkteco-biometric-integration/
├── Modules/
│   ├── API/            # REST API routes, controllers, resources
│   ├── Authentication/ # Login, tokens, guards
│   ├── Credentials/    # Device credential management
│   ├── Docs/           # Documentation module
│   ├── Pusher/         # Real-time push events
│   └── Receiver/       # MDB import & device sync engine
├── public/
│   ├── att2000.mdb     # Sample MDB file for testing
│   └── openapi.json    # OpenAPI spec
├── docs/               # Markdown documentation (this content)
└── database/
    └── migrations/     # Database schema
```

### Running tests

```bash
php artisan test
# or with coverage:
php artisan test --coverage
```

### Adding a new module

```bash
php artisan module:make MyModule
```

Each module has its own `routes/`, `app/`, `database/`, and `resources/` folders.

### Useful commands

```bash
php artisan module:list          # List all modules
php artisan route:list           # Show all registered routes
php artisan queue:work           # Process import jobs
php artisan tinker               # Laravel REPL for debugging
```

</details>

---

<details>
<summary><h2>🐛 Troubleshooting</h2></summary>

### MDB Import fails

**Cause**: `php_odbc` extension not installed or ODBC driver not found.

**Fix — Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php-odbc unixodbc mdbtools
# For a specific PHP version (e.g. 8.1):
sudo apt install php8.1-odbc
sudo systemctl restart apache2    # or php8.1-fpm
```

**Fix — Linux (RHEL/CentOS/Fedora):**
```bash
sudo dnf install php-odbc unixODBC mdbtools
sudo systemctl restart httpd
```

**Fix — Windows:**
1. Download and install [Microsoft Access Database Engine Redistributable](https://www.microsoft.com/en-us/download/details.aspx?id=54920)
2. In your `php.ini`, uncomment or add:
   ```ini
   extension=odbc
   ; or on older builds:
   extension=php_odbc.dll
   ```
3. Restart IIS / Apache / PHP-FPM

**Verify ODBC is loaded:**
```bash
php -m | grep odbc
php -r "var_dump(extension_loaded('odbc'));"
```

---

### Device connection fails

- Confirm the device IP and port (default `4370`) are correct
- Check that TCP port `4370` is not blocked by a firewall
- In the device admin panel, verify **Comm → IP** is configured correctly
- Ensure **Allow External Connections** (or equivalent) is enabled on the device

---

### Common errors

| Error | Likely Cause | Fix |
|---|---|---|
| `SQLSTATE[IM002]` — driver not found | ODBC driver missing | Install platform ODBC driver + enable `php_odbc` |
| Permission denied on `.mdb` | File permissions | Give web server read access to the MDB file |
| File format not recognized | Unsupported MDB version | Convert MDB to a newer format or use `mdbtools` to inspect |
| Timestamps off by hours | Timezone mismatch | Set `date.timezone` in `php.ini`; verify importer TZ config |
*** End Patch
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   I n s t a l l a t i o n < / s t r o n g > < / s u m m a r y > 
 
 
 
 F o l l o w   t h e s e   q u i c k   s t e p s   t o   g e t   t h e   p r o j e c t   r u n n i n g   l o c a l l y . 
 
 
 
 ` ` ` b a s h 
 
 g i t   c l o n e   h t t p s : / / g i t h u b . c o m / a r a f a t - a n w a r / z k t e c o - b i o m e t r i c - i n t e g r a t i o n . g i t 
 
 c d   z k t e c o - b i o m e t r i c - i n t e g r a t i o n 
 
 c o m p o s e r   i n s t a l l 
 
 c p   . e n v . e x a m p l e   . e n v 
 
 #   u p d a t e   . e n v   D B   s e t t i n g s 
 
 p h p   a r t i s a n   k e y : g e n e r a t e 
 
 p h p   a r t i s a n   m i g r a t e 
 
 n p m   i n s t a l l   #   o p t i o n a l   ( a s s e t s ) 
 
 n p m   r u n   b u i l d 
 
 p h p   a r t i s a n   s e r v e 
 
 ` ` ` 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   U s a g e   ( I m p o r t s   /   D e v i c e s   /   A P I ) < / s t r o n g > < / s u m m a r y > 
 
 
 
 # #   I m p o r t s   ( U I ) 
 
 
 
 O p e n   t h e   a p p   i n   a   b r o w s e r ,   s i g n   i n   a s   a n   a d m i n / o p e r a t o r   a n d   g o   t o   * * I m p o r t s * *   ( o r   * * R e c e i v e r   �� �   I m p o r t * * ) .   U s e   t h e   U p l o a d   c o n t r o l   t o   a d d   a n   M D B   f i l e   o r   u s e   t h e   s e r v e r   s a m p l e   ` a t t 2 0 0 0 . m d b ` .   T h e   U I   r u n s   t h e   i m p o r t   j o b   a n d   s h o w s   p r o g r e s s   a n d   h i s t o r y . 
 
 
 
 # #   D e v i c e s   ( U I ) 
 
 
 
 N a v i g a t e   t o   * * D e v i c e s * *   t o   a d d   o r   e d i t   Z K T e c o   d e v i c e s   ( I P ,   p o r t ,   n a m e ,   c r e d e n t i a l s ) .   U s e   t h e   * * S y n c * *   a c t i o n   t o   p u l l   l o g s ;   t h e   U I   s h o w s   l a s t - s y n c   a n d   r e s u l t s . 
 
 
 
 # #   A P I 
 
 
 
 T h e   R E S T   A P I   i s   d e s c r i b e d   b y   t h e   O p e n A P I   s p e c   a t   ` / o p e n a p i . j s o n ` .   C o m m o n   e n d p o i n t s : 
 
 
 
 -   ` G E T   / a p i / u s e r s `   �� �   l i s t   u s e r s 
 
 -   ` G E T   / a p i / a t t e n d a n c e s `   �� �   l i s t   a t t e n d a n c e   e v e n t s 
 
 -   ` P O S T   / a p i / i m p o r t / m d b `   �� �   u p l o a d   a n d   i m p o r t   a n   M D B   ( i f   A P I   i m p o r t   i s   e n a b l e d ) 
 
 
 
 A u t h e n t i c a t i o n :   i n c l u d e   ` A u t h o r i z a t i o n :   B e a r e r   < T O K E N > `   i n   r e q u e s t s . 
 
 
 
 E x a m p l e :   l i s t   a t t e n d a n c e s 
 
 
 
 ` ` ` b a s h 
 
 c u r l   - X   G E T   " h t t p : / / l o c a l h o s t : 8 0 0 0 / a p i / a t t e n d a n c e s "   \ 
 
 	 - H   " A u t h o r i z a t i o n :   B e a r e r   < T O K E N > "   \ 
 
 	 - H   " A c c e p t :   a p p l i c a t i o n / j s o n " 
 
 ` ` ` 
 
 
 
 E x a m p l e :   u p l o a d   a n   M D B 
 
 
 
 ` ` ` b a s h 
 
 c u r l   - X   P O S T   " h t t p : / / l o c a l h o s t : 8 0 0 0 / a p i / i m p o r t / m d b "   \ 
 
 	 - H   " A u t h o r i z a t i o n :   B e a r e r   < T O K E N > "   \ 
 
 	 - F   " f i l e = @ / p a t h / t o / a t t 2 0 0 0 . m d b " 
 
 ` ` ` 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   M D B   F o r m a t < / s t r o n g > < / s u m m a r y > 
 
 
 
 T h e   i n c l u d e d   ` a t t 2 0 0 0 . m d b `   s a m p l e   c o n t a i n s   t y p i c a l   t a b l e s   u s e d   b y   Z K T e c o   e x p o r t s .   K e y   c o l u m n s   y o u   w i l l   f i n d : 
 
 
 
 -   ` U S E R I D `   /   ` P I N `   �� �   u n i q u e   u s e r   i d e n t i f i e r 
 
 -   ` N A M E `   �� �   u s e r   f u l l   n a m e 
 
 -   ` V E R I F Y M O D E `   �� �   v e r i f i c a t i o n   m e t h o d 
 
 -   ` C H E C K T I M E `   /   ` A T T _ T I M E `   �� �   t i m e s t a m p   o f   a t t e n d a n c e 
 
 -   ` C H E C K T Y P E `   �� �   i n / o u t   o r   e v e n t   t y p e 
 
 
 
 I m p o r t e r   b e h a v i o r : 
 
 
 
 1 .   O p e n   M D B   a n d   e n u m e r a t e   t a b l e s . 
 
 2 .   R e a d   u s e r   t a b l e   t o   c r e a t e / u p d a t e   l o c a l   u s e r s . 
 
 3 .   R e a d   a t t e n d a n c e / l o g   t a b l e   a n d   i n s e r t   a t t e n d a n c e   e v e n t s . 
 
 4 .   P o s t - p r o c e s s :   d e d u p e ,   n o r m a l i z e   t i m e s t a m p s   ( U T C   o r   c o n f i g u r e d   T Z ) ,   a n d   i n d e x . 
 
 
 
 I f   y o u   m u s t   s u p p o r t   a l t e r n a t e   M D B   s c h e m a s ,   a d d   a   c o l u m n   m a p p i n g   i n   t h e   i m p o r t e r   c o n f i g u r a t i o n . 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   Z K T e c o   D e v i c e s < / s t r o n g > < / s u m m a r y > 
 
 
 
 Z K T e c o   d e v i c e s   t y p i c a l l y   a c c e p t   n e t w o r k   q u e r i e s   o n   T C P   p o r t   ` 4 3 7 0 ` .   T h i s   p r o j e c t   s u p p o r t s : 
 
 
 
 -   P u l l i n g   u s e r s   a n d   l o g s   v i a   d e v i c e   p r o t o c o l / S D K 
 
 -   E x p o r t - t o - M D B   w o r k f l o w   ( v e n d o r   t o o l s )   a n d   s u b s e q u e n t   i m p o r t 
 
 
 
 N o t e s : 
 
 
 
 -   T r a c k   ` l a s t _ s y n c `   p e r   d e v i c e   t o   a v o i d   d u p l i c a t e s . 
 
 -   I m p l e m e n t   r e t r i e s   a n d   b a c k o f f   f o r   f l a k y   n e t w o r k s . 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   A P I   D o c u m e n t a t i o n < / s t r o n g > < / s u m m a r y > 
 
 
 
 T h e   O p e n A P I   s p e c   i s   a t   ` p u b l i c / o p e n a p i . j s o n ` .   T o   v i e w : 
 
 
 
 -   O p e n   ` h t t p : / / l o c a l h o s t : 8 0 0 0 / o p e n a p i . j s o n `   w i t h   S w a g g e r   U I ,   o r 
 
 -   L o a d   ` p u b l i c / o p e n a p i . j s o n `   a t   h t t p : / / e d i t o r . s w a g g e r . i o / . 
 
 
 
 T h e   s p e c   i n c l u d e s   p a r a m e t e r   d e s c r i p t i o n s ,   r e s p o n s e   s c h e m a s ,   a n d   a u t h   r e q u i r e m e n t s .   U s e   i t   t o   g e n e r a t e   c l i e n t s   o r   a   P o s t m a n   c o l l e c t i o n . 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   D e v e l o p m e n t   N o t e s < / s t r o n g > < / s u m m a r y > 
 
 
 
 -   M o d u l e s   a r e   u n d e r   ` M o d u l e s / `   ( e . g . ,   ` M o d u l e s / R e c e i v e r ` ,   ` M o d u l e s / A u t h e n t i c a t i o n ` ) . 
 
 -   I n s p e c t   ` r o u t e s / `   a n d   ` a p p / `   i n s i d e   e a c h   m o d u l e   f o r   c o n t r o l l e r s   a n d   c o m m a n d s . 
 
 -   S a m p l e   M D B   f o r   l o c a l   t e s t i n g :   ` p u b l i c / a t t 2 0 0 0 . m d b ` . 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�#Q%  T r o u b l e s h o o t i n g   &   O D B C < / s t r o n g > < / s u m m a r y > 
 
 
 
 -   M D B   i m p o r t   i s s u e s :   e n s u r e   t h e   P H P   e n v i r o n m e n t   h a s   a n   M D B - r e a d i n g   l i b r a r y   a v a i l a b l e   ( e . g . ,   ` u o d b c ` ,   ` m d b t o o l s `   o n   L i n u x ,   o r   u s e   a   P H P   M D B   r e a d e r   p a c k a g e ) .   O n   W i n d o w s ,   O D B C   d r i v e r s   c a n   b e   u s e d .   A l s o   e n s u r e   t h e   ` p h p _ o d b c `   e x t e n s i o n   i s   e n a b l e d   i n   y o u r   ` p h p . i n i `   a n d   t h e   w e b   s e r v e r / P H P - F P M   h a s   b e e n   r e s t a r t e d . 
 
 -   D e v i c e   c o n n e c t i o n   i s s u e s :   v e r i f y   n e t w o r k   c o n n e c t i v i t y   a n d   d e v i c e   I P / p o r t ,   c h e c k   f i r e w a l l   r u l e s ,   a n d   c o n f i r m   d e v i c e   a l l o w s   r e m o t e   q u e r i e s . 
 
 
 
 # # #   E n a b l i n g   ` p h p _ o d b c `   ( p l a t f o r m - s p e c i f i c ) 
 
 
 
 I f   M D B   i m p o r t   f a i l s ,   m a k e   s u r e   t h e   P H P   O D B C   e x t e n s i o n   i s   i n s t a l l e d   a n d   t h e   s y s t e m   h a s   a n   O D B C   d r i v e r   f o r   A c c e s s   ( o r   ` m d b t o o l s `   f o r   L i n u x ) . 
 
 
 
 -   D e b i a n   /   U b u n t u : 
 
 
 
 ` ` ` b a s h 
 
 s u d o   a p t   u p d a t e 
 
 s u d o   a p t   i n s t a l l   p h p - o d b c   u n i x o d b c   m d b t o o l s 
 
 #   I f   u s i n g   a   s p e c i f i c   P H P   v e r s i o n   ( r e p l a c e   8 . 1   a s   n e e d e d ) : 
 
 s u d o   a p t   i n s t a l l   p h p 8 . 1 - o d b c 
 
 s u d o   s y s t e m c t l   r e s t a r t   a p a c h e 2         #   o r   p h p 8 . 1 - f p m 
 
 ` ` ` 
 
 
 
 -   R H E L   /   C e n t O S   /   F e d o r a : 
 
 
 
 ` ` ` b a s h 
 
 s u d o   d n f   i n s t a l l   p h p - o d b c   u n i x O D B C   m d b t o o l s 
 
 s u d o   s y s t e m c t l   r e s t a r t   h t t p d           #   o r   p h p - f p m 
 
 ` ` ` 
 
 
 
 -   W i n d o w s : 
 
 
 
 1 .   I n s t a l l   t h e   M i c r o s o f t   A c c e s s   D a t a b a s e   E n g i n e   ( A C E )   /   M i c r o s o f t   A c c e s s   O D B C   d r i v e r s   ( M i c r o s o f t   A c c e s s   D a t a b a s e   E n g i n e   R e d i s t r i b u t a b l e )   s o   t h e   O S   c a n   r e a d   ` . m d b `   f i l e s . 
 
 2 .   E d i t   y o u r   ` p h p . i n i `   ( u s e d   b y   t h e   w e b s e r v e r / P H P )   a n d   e n a b l e   t h e   O D B C   e x t e n s i o n   b y   a d d i n g   o r   u n c o m m e n t i n g   o n e   o f   t h e   l i n e s   b e l o w   ( e x a c t   n a m e   v a r i e s   b y   P H P   b u i l d ) : 
 
 
 
 ` ` ` 
 
 e x t e n s i o n = o d b c 
 
 ;   o r 
 
 e x t e n s i o n = p h p _ o d b c . d l l 
 
 ` ` ` 
 
 
 
 3 .   R e s t a r t   I I S ,   A p a c h e   o r   P H P - F P M   a n d   v e r i f y   t h e   e x t e n s i o n   i s   l o a d e d : 
 
 
 
 ` ` ` b a s h 
 
 p h p   - m   |   g r e p   o d b c 
 
 p h p   - r   " v a r _ d u m p ( e x t e n s i o n _ l o a d e d ( ' o d b c ' ) ) ; " 
 
 ` ` ` 
 
 
 
 < / d e t a i l s > 
 
 
 
 < d e t a i l s > 
 
 < s u m m a r y > < s t r o n g > a"�� �   C o n t r i b u t i n g < / s t r o n g > < / s u m m a r y > 
 
 
 
 -   F o r k ,   a d d   a   f e a t u r e   b r a n c h ,   i n c l u d e   t e s t s ,   a n d   o p e n   a   P R   a g a i n s t   ` r e l e a s e ` . 
 
 
 
 < / d e t a i l s > 
 
 
 
 
