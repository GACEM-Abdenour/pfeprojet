# Connection Strings - GIA Project

## Your SQL Server Setup
- **Instance:** SQLEXPRESS
- **Authentication:** Windows Authentication (Integrated Security)
- **Database:** GIA_IncidentDB

---

## 1. PowerShell Scripts (setup_database.ps1, run_test_data.ps1)

**Command:**
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

**Or:**
```powershell
.\setup_database.ps1 -ServerInstance "SQLEXPRESS" -UseWindowsAuth
```

**Connection String (internal):**
```
Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;Integrated Security=True;
```

---

## 2. PHP (includes/db.php)

**Current Configuration:**
```php
define('DB_HOST', 'localhost\SQLEXPRESS');
define('DB_NAME', 'GIA_IncidentDB');
define('DB_USE_WINDOWS_AUTH', true);
```

**DSN Format:**
```php
$dsn = "sqlsrv:Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;CharacterSet=UTF-8";
```

**Connection (Windows Auth):**
```php
$pdo = new PDO($dsn, null, null, $options);
```

---

## 3. SQL Server Management Studio

**Connection Dialog:**
- Server Name: `SQLEXPRESS` or `localhost\SQLEXPRESS`
- Authentication: Windows Authentication
- Database Name: `GIA_IncidentDB`

**Connection String (for reference):**
```
Data Source=localhost\SQLEXPRESS;Initial Catalog=GIA_IncidentDB;Integrated Security=True;Encrypt=True;TrustServerCertificate=False
```

---

## 4. ADO.NET / .NET (PowerShell scripts use this)

**Connection String:**
```
Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;Integrated Security=True;
```

**With Encryption (if needed):**
```
Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;Integrated Security=True;Encrypt=True;TrustServerCertificate=True
```

---

## 5. sqlcmd Command Line

**Command:**
```powershell
sqlcmd -S localhost\SQLEXPRESS -d GIA_IncidentDB -E
```

**With SQL File:**
```powershell
sqlcmd -S localhost\SQLEXPRESS -d GIA_IncidentDB -E -i "test\insert_test_data.sql"
```

**Parameters:**
- `-S` = Server instance
- `-d` = Database name
- `-E` = Windows Authentication (Trusted Connection)

---

## Summary: Correct Values for Your Setup

| Component | Value |
|-----------|-------|
| Server Instance | `localhost\SQLEXPRESS` or `SQLEXPRESS` |
| Database Name | `GIA_IncidentDB` |
| Authentication | Windows Authentication (Integrated Security) |
| Username | (empty - uses Windows account) |
| Password | (empty - uses Windows account) |

---

## Quick Reference Commands

### Database Setup
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

### Insert Test Data (PowerShell)
```powershell
.\run_test_data.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

### Insert Test Data (sqlcmd)
```powershell
sqlcmd -S localhost\SQLEXPRESS -d GIA_IncidentDB -E -i "test\insert_test_data.sql"
```

### Test PHP Connection
```powershell
php -r "require 'includes/db.php'; var_dump(getDBConnection());"
```

---

## Troubleshooting

### If "localhost\SQLEXPRESS" doesn't work:
Try just `SQLEXPRESS`:
```powershell
.\setup_database.ps1 -ServerInstance "SQLEXPRESS" -UseWindowsAuth
```

### If you need encryption:
Add to connection string:
```
Encrypt=True;TrustServerCertificate=True
```

### If connection fails:
1. Check SQL Server is running: `Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}`
2. Verify instance name: `.\check_sql_server.ps1`
3. Test connection: `sqlcmd -S localhost\SQLEXPRESS -E`

---

**Your correct connection string format:**
```
Server=localhost\SQLEXPRESS;Database=GIA_IncidentDB;Integrated Security=True;
```
