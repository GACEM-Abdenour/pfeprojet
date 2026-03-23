# Fix SQL Server Connection Error (Error 53)

## Error You're Seeing
```
Error 53: The network path was not found
A network-related or instance-specific error occurred while establishing a connection to SQL Server
```

---

## Solution Steps

### Step 1: Check SQL Server Instance Name

The error suggests SSMS can't find the server. Try these server names:

**Option A: Full Server Name**
```
localhost\SQLEXPRESS
```

**Option B: Computer Name**
```
vivobook16s\SQLEXPRESS
```

**Option C: Just Instance Name (if on same machine)**
```
.\SQLEXPRESS
```

**Option D: IP Address**
```
127.0.0.1\SQLEXPRESS
```

---

### Step 2: Start SQL Server Browser Service

The Browser service helps SSMS find named instances:

```powershell
# Check if Browser service exists
Get-Service | Where-Object {$_.Name -like "*SQLBrowser*"}

# Start Browser service
Start-Service "SQLBrowser"
```

**Or via Services:**
1. Press `Windows + R`
2. Type `services.msc`
3. Find "SQL Server Browser"
4. Right-click → Start
5. Set Startup Type to "Automatic"

---

### Step 3: Enable Named Pipes Protocol

1. Open **SQL Server Configuration Manager**
   - Press `Windows + R`
   - Type: `SQLServerManager16.msc` (or `SQLServerManager15.msc` for older versions)
   - Press Enter

2. Navigate to:
   - **SQL Server Network Configuration** → **Protocols for SQLEXPRESS**

3. Enable:
   - ✅ **Named Pipes** (Right-click → Enable)
   - ✅ **TCP/IP** (Right-click → Enable)

4. **Restart SQL Server Service:**
   - Go to **SQL Server Services**
   - Right-click **SQL Server (SQLEXPRESS)** → Restart

---

### Step 4: Try Different Connection Methods

**In SQL Server Management Studio Connect Dialog:**

**Method 1: Use Computer Name**
- Server name: `vivobook16s\SQLEXPRESS`
- Authentication: Windows Authentication
- Database: `<default>` (leave as default for now)

**Method 2: Use Localhost**
- Server name: `localhost\SQLEXPRESS`
- Authentication: Windows Authentication
- Database: `<default>`

**Method 3: Use Dot Notation**
- Server name: `.\SQLEXPRESS`
- Authentication: Windows Authentication
- Database: `<default>`

**Method 4: Use IP**
- Server name: `127.0.0.1\SQLEXPRESS`
- Authentication: Windows Authentication
- Database: `<default>`

---

### Step 5: Verify SQL Server is Running

```powershell
# Check SQL Server service status
Get-Service | Where-Object {$_.DisplayName -like "*SQL Server (SQLEXPRESS)*"}

# If stopped, start it:
Start-Service "MSSQL$SQLEXPRESS"
```

---

### Step 6: Test Connection via Command Line

```powershell
# Test with sqlcmd
sqlcmd -S localhost\SQLEXPRESS -E

# If that works, you should see:
# 1>
# Type: SELECT @@VERSION
# Press Enter twice
# Type: GO
# Press Enter
```

---

## Quick Fix Checklist

- [ ] SQL Server (SQLEXPRESS) service is **Running**
- [ ] SQL Server Browser service is **Started** (and set to Automatic)
- [ ] Named Pipes protocol is **Enabled** in SQL Server Configuration Manager
- [ ] TCP/IP protocol is **Enabled** in SQL Server Configuration Manager
- [ ] SQL Server service was **Restarted** after enabling protocols
- [ ] Try server name: `localhost\SQLEXPRESS` or `vivobook16s\SQLEXPRESS`

---

## Most Common Fix

**90% of the time, this fixes it:**

1. **Start SQL Server Browser:**
   ```powershell
   Start-Service "SQLBrowser"
   Set-Service "SQLBrowser" -StartupType Automatic
   ```

2. **In SSMS, use full server name:**
   - Server: `localhost\SQLEXPRESS`
   - Auth: Windows Authentication
   - Database: `<default>` (for initial connection)

3. **After connecting, change database:**
   - Right-click server in Object Explorer
   - Select "New Query"
   - Type: `USE GIA_IncidentDB;`
   - Execute (F5)

---

## Alternative: Connect Without Browser Service

If Browser service won't start, use **TCP/IP with port**:

1. Find SQL Server port:
   - SQL Server Configuration Manager
   - SQL Server Network Configuration → Protocols for SQLEXPRESS
   - TCP/IP → Properties → IP Addresses tab
   - Find "IPAll" section
   - Note the "TCP Dynamic Ports" or "TCP Port" (usually 1433 or dynamic)

2. Connect using port:
   - Server name: `localhost,1433` (or the port number you found)
   - Auth: Windows Authentication

---

## Still Not Working?

Run this diagnostic script:

```powershell
.\check_sql_server.ps1

# Check if you can connect via .NET (like our PowerShell scripts do)
$conn = New-Object System.Data.SqlClient.SqlConnection
$conn.ConnectionString = "Server=localhost\SQLEXPRESS;Integrated Security=True;"
try {
    $conn.Open()
    Write-Host "[OK] Connection works!" -ForegroundColor Green
    $conn.Close()
} catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
}
```

---

**Try these steps in order - the Browser service fix usually resolves Error 53!**
