# Troubleshooting

## Table of contents

- [ODBC and php_odbc](#odbc-and-php_odbc)
- [Useful links](#useful-links)
- [Common errors](#common-errors)

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
