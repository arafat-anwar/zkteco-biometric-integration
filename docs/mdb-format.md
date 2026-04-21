# MDB Format

## Table of contents

- [Key columns](#key-columns)
- [Importer behavior](#importer-behavior)

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
