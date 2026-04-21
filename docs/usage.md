# Usage

## Table of contents

- [Imports (UI)](#imports-ui)
- [Devices (UI)](#devices-ui)
- [API](#api)

## Imports (UI)

Open the app in a browser, sign in as an admin/operator and go to **Imports** (or **Receiver → Import**). Use the Upload control to add an MDB file or use the server sample `att2000.mdb`. The UI runs the import job and shows progress and history.

## Devices (UI)

Navigate to **Devices** to add or edit ZKTeco devices (IP, port, name, credentials). Use the **Sync** action to pull logs; the UI shows last-sync and results.

## API

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
