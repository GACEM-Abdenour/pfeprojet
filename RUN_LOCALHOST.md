# Running GIA Application on Localhost

**Guide:** Complete instructions for running the GIA Incident Management Platform locally

---

## Prerequisites

Before running the application, ensure you have:

1. ✅ **SQL Server Express** installed and running
2. ✅ **PHP 7.4+** installed with PDO_SQLSRV extension
3. ✅ **Web Server** (IIS, Apache, or PHP built-in server)

---

## Step 1: Database Setup

### 1.1 Install SQL Server Express (if not installed)

1. Download SQL Server Express from Microsoft
2. Install with default instance or `SQLEXPRESS`
3. Enable SQL Server Authentication (if needed)
4. Start SQL Server service:
   ```powershell
   Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}
   # If stopped, start it:
   Start-Service "MSSQLSERVER"  # or "MSSQL$SQLEXPRESS"
   ```

### 1.2 Configure Database Connection

Edit `includes/db.php` and update credentials:

```php
define('DB_HOST', 'localhost\SQLEXPRESS'); // or 'localhost' for default instance
define('DB_NAME', 'GIA_IncidentDB');
define('DB_USER', 'sa'); // or your SQL Server username
define('DB_PASS', 'YourPassword123'); // your SQL Server password
```

### 1.3 Create Database and Tables

Run the PowerShell setup script:

```powershell
cd c:\Users\abdeg\Documents\codeProjects\sonalgaz
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
# OR with SQL Authentication:
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -Username "sa" -Password "YourPassword"
```

### 1.4 Insert Test Data

```powershell
php test/insert_test_data.php
```

This will create:
- 6 test users (see `TEST_DATA.md` for credentials)
- 8 test incidents
- 3 test attachments
- 7 test log entries

---

## Step 2: PHP Setup

### 2.1 Install PHP

1. Download PHP 7.4 or 8.x from [php.net](https://windows.php.net/download/)
2. Extract to `C:\php` (or your preferred location)
3. Add PHP to PATH:
   ```powershell
   # Add to system PATH environment variable
   # Or use: $env:Path += ";C:\php"
   ```

### 2.2 Install PDO_SQLSRV Extension

1. Download Microsoft Drivers for PHP for SQL Server:
   - [Download Link](https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)
2. Extract and copy DLL files to PHP `ext` directory:
   - `php_pdo_sqlsrv_74_ts.dll` → `C:\php\ext\`
   - `php_sqlsrv_74_ts.dll` → `C:\php\ext\`
3. Edit `php.ini`:
   ```ini
   extension=pdo_sqlsrv
   extension=sqlsrv
   ```

### 2.3 Verify PHP Installation

```powershell
php -v
php -m | findstr sqlsrv
```

---

## Step 3: Run Web Server

### Option A: PHP Built-in Server (Easiest for Testing)

```powershell
cd c:\Users\abdeg\Documents\codeProjects\sonalgaz
php -S localhost:8000
```

Then open browser: **http://localhost:8000/pages/login.php**

**Note:** PHP built-in server may have issues with relative paths. Use absolute paths or configure document root:

```powershell
php -S localhost:8000 -t .
```

### Option B: IIS (Windows Server 2019)

1. **Enable IIS:**
   ```powershell
   Enable-WindowsOptionalFeature -Online -FeatureName IIS-WebServerRole
   Enable-WindowsOptionalFeature -Online -FeatureName IIS-WebServer
   Enable-WindowsOptionalFeature -Online -FeatureName IIS-CommonHttpFeatures
   Enable-WindowsOptionalFeature -Online -FeatureName IIS-HttpErrors
   Enable-WindowsOptionalFeature -Online -FeatureName IIS-ApplicationInit
   ```

2. **Install PHP Manager for IIS:**
   - Download from [phpmanager.codeplex.com](https://phpmanager.codeplex.com/)
   - Install and configure PHP

3. **Configure IIS Site:**
   - Open IIS Manager
   - Add new website:
     - **Site name:** GIA
     - **Physical path:** `C:\Users\abdeg\Documents\codeProjects\sonalgaz`
     - **Binding:** `http://localhost:80` (or custom port)
   - Set default document: `index.php`

4. **Access:** **http://localhost/pages/login.php**

### Option C: XAMPP/WAMP

1. Install XAMPP or WAMP
2. Copy project to `htdocs` or `www` directory
3. Start Apache service
4. Access: **http://localhost/sonalgaz/pages/login.php**

---

## Step 4: Test Login

### 4.1 Access Login Page

Open browser and navigate to:
- **PHP Built-in:** http://localhost:8000/pages/login.php
- **IIS:** http://localhost/pages/login.php
- **XAMPP:** http://localhost/sonalgaz/pages/login.php

### 4.2 Test Credentials

See `TEST_DATA.md` for complete list. Quick reference:

| Role | Username | Password | Redirects To |
|------|----------|----------|--------------|
| Admin | `admin` | `admin123` | `admin_dashboard.php` |
| Technician | `tech1` | `tech123` | `tech_dashboard.php` |
| Reporter | `reporter1` | `user123` | `create_ticket.php` |

### 4.3 Expected Behavior

1. **Successful Login:**
   - Redirects to appropriate dashboard based on role
   - Session is created
   - Can navigate between pages

2. **Failed Login:**
   - Shows error message: "Nom d'utilisateur ou mot de passe incorrect"
   - Stays on login page
   - Form fields remain

---

## Step 5: Troubleshooting

### Database Connection Error

**Error:** "Database connection failed"

**Solutions:**
1. Check SQL Server is running:
   ```powershell
   Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}
   ```
2. Verify credentials in `includes/db.php`
3. Test connection:
   ```php
   php -r "require 'includes/db.php'; var_dump(getDBConnection());"
   ```

### PHP Extension Not Found

**Error:** "Call to undefined function sqlsrv_connect()"

**Solutions:**
1. Verify extension is loaded:
   ```powershell
   php -m | findstr sqlsrv
   ```
2. Check `php.ini` has correct extension lines
3. Ensure DLL files match PHP version (TS vs NTS, 7.4 vs 8.x)

### Session Not Working

**Error:** "Warning: session_start()"

**Solutions:**
1. Check `session.save_path` in `php.ini` is writable
2. Ensure `session_start()` is called before any output
3. Check browser cookies are enabled

### Path Issues

**Error:** "File not found" or CSS/JS not loading

**Solutions:**
1. Use absolute paths or `__DIR__` constant
2. Check web server document root
3. Verify file paths in `login.php` are correct relative to web root

### Port Already in Use

**Error:** "Address already in use"

**Solutions:**
1. Use different port:
   ```powershell
   php -S localhost:8080
   ```
2. Or stop the service using the port:
   ```powershell
   netstat -ano | findstr :8000
   taskkill /PID <PID> /F
   ```

---

## Quick Start Commands

### Complete Setup (Copy & Paste)

```powershell
# 1. Navigate to project
cd c:\Users\abdeg\Documents\codeProjects\sonalgaz

# 2. Setup database (update credentials first!)
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth

# 3. Insert test data
php test/insert_test_data.php

# 4. Start PHP server
php -S localhost:8000

# 5. Open browser
Start-Process "http://localhost:8000/pages/login.php"
```

---

## File Structure for Web Server

```
sonalgaz/                    (Web Root)
├── index.php                (Router - redirects to login or dashboard)
├── pages/
│   └── login.php           (Login page)
├── includes/
│   ├── db.php              (Database connection)
│   ├── auth.php            (Authentication handler)
│   ├── functions.php       (Helper functions)
│   └── logout.php          (Logout handler)
├── database/
│   └── schema.sql          (Database schema)
├── src/
│   └── assets/             (NiceAdmin CSS/JS - must be accessible)
└── uploads/                 (File uploads directory - create if needed)
```

---

## Security Notes

⚠️ **For Production:**
- Change all default passwords
- Use strong passwords for SQL Server
- Enable HTTPS
- Configure proper file permissions
- Remove test data
- Set proper error reporting (hide errors in production)

---

## Next Steps After Login Works

1. ✅ Test login with different roles
2. ✅ Create dashboard pages (`tech_dashboard.php`, `admin_dashboard.php`)
3. ✅ Implement ticket creation (`create_ticket.php`)
4. ✅ Add ticket viewing and management
5. ✅ Implement file uploads
6. ✅ Add incident logging functionality

---

**Last Updated:** February 12, 2026
