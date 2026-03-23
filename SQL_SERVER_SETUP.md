# SQL Server Setup Guide - Troubleshooting

## Current Issue: SQL Server Not Found

The database setup script cannot connect to SQL Server because:
- SQL Server is not installed, OR
- SQL Server is installed but not running, OR
- SQL Server instance name is incorrect

---

## Step 1: Check if SQL Server is Installed

### Method 1: Check Services
```powershell
Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}
```

**If you see services like:**
- `SQL Server (MSSQLSERVER)` → Use instance name: `localhost`
- `SQL Server (SQLEXPRESS)` → Use instance name: `localhost\SQLEXPRESS`
- `SQL Server (MSSQL$INSTANCENAME)` → Use instance name: `localhost\INSTANCENAME`

### Method 2: Check Installation
```powershell
Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Microsoft SQL Server\Instance Names\SQL" -ErrorAction SilentlyContinue
```

---

## Step 2: If SQL Server is Installed but Not Running

### Start SQL Server Service

**For Default Instance:**
```powershell
Start-Service "MSSQLSERVER"
```

**For SQLEXPRESS Instance:**
```powershell
Start-Service "MSSQL$SQLEXPRESS"
```

**Or use Services GUI:**
1. Press `Windows + R`
2. Type `services.msc` and press Enter
3. Find "SQL Server (MSSQLSERVER)" or "SQL Server (SQLEXPRESS)"
4. Right-click → Start

---

## Step 3: If SQL Server is NOT Installed

### Install SQL Server Express (Free)

1. **Download SQL Server Express:**
   - Go to: https://www.microsoft.com/en-us/sql-server/sql-server-downloads
   - Download "Express" edition (Free)
   - File size: ~1.5 GB

2. **Install SQL Server Express:**
   - Run the installer
   - Choose "Basic" installation (easiest)
   - Or choose "Custom" for more control
   - **Important:** Note the instance name (usually `SQLEXPRESS`)

3. **During Installation:**
   - Choose "Mixed Mode Authentication" (SQL Server + Windows Auth)
   - Set a password for `sa` account (remember this!)
   - Complete the installation

4. **After Installation:**
   - SQL Server should start automatically
   - Verify it's running:
     ```powershell
     Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}
     ```

---

## Step 4: Run Database Setup

### Option A: Windows Authentication (Recommended)

**If SQL Server is running with default instance:**
```powershell
.\setup_database.ps1 -ServerInstance "localhost" -UseWindowsAuth
```

**If SQL Server Express (SQLEXPRESS instance):**
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

### Option B: SQL Server Authentication

**If you set up SQL Server with `sa` password:**
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -Username "sa" -Password "YourPassword"
```

Replace `YourPassword` with the actual password you set during installation.

---

## Step 5: Verify Connection

After SQL Server is running, test the connection:

```powershell
# Test connection
$conn = New-Object System.Data.SqlClient.SqlConnection
$conn.ConnectionString = "Server=localhost\SQLEXPRESS;Integrated Security=True;"
try {
    $conn.Open()
    Write-Host "[OK] Connected successfully!" -ForegroundColor Green
    $conn.Close()
} catch {
    Write-Host "[ERROR] Connection failed: $($_.Exception.Message)" -ForegroundColor Red
}
```

---

## Common Instance Names

| Installation Type | Instance Name | Command |
|-------------------|---------------|---------|
| Default Instance | `localhost` | `.\setup_database.ps1 -ServerInstance "localhost" -UseWindowsAuth` |
| SQL Express | `localhost\SQLEXPRESS` | `.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth` |
| Named Instance | `localhost\INSTANCENAME` | `.\setup_database.ps1 -ServerInstance "localhost\INSTANCENAME" -UseWindowsAuth` |

---

## Quick Commands Reference

### Check SQL Server Status
```powershell
Get-Service | Where-Object {$_.DisplayName -like "*SQL*"} | Format-Table DisplayName, Status, Name -AutoSize
```

### Start SQL Server (if installed)
```powershell
# Default instance
Start-Service "MSSQLSERVER"

# Express instance
Start-Service "MSSQL$SQLEXPRESS"
```

### Stop SQL Server
```powershell
Stop-Service "MSSQLSERVER"
# or
Stop-Service "MSSQL$SQLEXPRESS"
```

### Test Connection
```powershell
.\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
```

---

## Troubleshooting

### Error: "Server was not found"
- **Solution:** SQL Server is not installed or not running
- **Action:** Install SQL Server Express or start the service

### Error: "Login failed"
- **Solution:** Wrong credentials or authentication mode
- **Action:** Try Windows Authentication or verify SQL Auth credentials

### Error: "Cannot open database"
- **Solution:** Database doesn't exist yet (this is normal)
- **Action:** The script will create it automatically

### Error: "Access denied"
- **Solution:** Insufficient permissions
- **Action:** Run PowerShell as Administrator or use an account with DB creation rights

---

## Next Steps After SQL Server is Running

1. **Run database setup:**
   ```powershell
   .\setup_database.ps1 -ServerInstance "localhost\SQLEXPRESS" -UseWindowsAuth
   ```

2. **Insert test data:**
   ```powershell
   php test/insert_test_data.php
   ```

3. **Start web server:**
   ```powershell
   php -S localhost:8000
   ```

4. **Test login:**
   - Open: http://localhost:8000/pages/login.php
   - Login with: `admin` / `admin123`

---

**Need Help?** Check `RUN_LOCALHOST.md` for complete setup instructions.
