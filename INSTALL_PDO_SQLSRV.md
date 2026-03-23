# Installing PDO_SQLSRV Extension for PHP

## Current Issue

PHP is installed (8.5.1) but PDO_SQLSRV extension is missing. This extension is required to connect to SQL Server.

---

## Quick Installation Guide

### Step 1: Download Microsoft Drivers for PHP

1. Go to: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
2. Download: **Microsoft Drivers for PHP for SQL Server**
3. Choose version matching your PHP:
   - PHP 8.5 → Download drivers for PHP 8.x
   - File: `ODBC Driver 18 for SQL Server` + PHP drivers

### Step 2: Find Your PHP Installation

```powershell
php --ini
# Look for "Loaded Configuration File" path
# Example: C:\php\php.ini
```

### Step 3: Extract and Copy DLL Files

1. Extract the downloaded drivers
2. Copy DLL files to PHP `ext` directory:
   - `php_pdo_sqlsrv_85_nts.dll` → Rename to `php_pdo_sqlsrv.dll`
   - `php_sqlsrv_85_nts.dll` → Rename to `php_sqlsrv.dll`
   - Copy to: `C:\php\ext\` (or your PHP ext directory)

**Important:** Use NTS (Non-Thread-Safe) version for PHP CLI, or TS (Thread-Safe) for web server.

### Step 4: Enable Extensions in php.ini

Edit `php.ini` and add:

```ini
extension=pdo_sqlsrv
extension=sqlsrv
```

### Step 5: Verify Installation

```powershell
php -m | findstr -i sqlsrv
```

Should show:
```
pdo_sqlsrv
sqlsrv
```

---

## Alternative: Use SQL Server via ODBC

If PDO_SQLSRV installation is problematic, you can use ODBC:

1. Install ODBC Driver 18 for SQL Server
2. Use `pdo_odbc` instead (requires connection string changes)

---

## Quick Test

After installation, test connection:

```powershell
php -r "require 'includes/db.php'; var_dump(getDBConnection());"
```

---

## Troubleshooting

### Error: "extension not loaded"
- Check DLL files are in correct `ext` directory
- Verify `php.ini` has extension lines
- Check DLL architecture matches PHP (x64 vs x86)

### Error: "Unable to load dynamic library"
- DLL version mismatch (8.5 vs 8.4, NTS vs TS)
- Missing dependencies (Visual C++ Redistributable)

### Error: "Driver not found"
- ODBC Driver 18 for SQL Server not installed
- Install from: https://aka.ms/downloadmsodbcsql

---

**After installation, run:**
```powershell
php test/insert_test_data.php
```
