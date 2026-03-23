# PowerShell Script to Setup GIA Incident Management Database
# Uses .NET System.Data.SqlClient to connect to SQL Server

param(
    [string]$ServerInstance = "localhost",
    [string]$Database = "GIA_IncidentDB",
    [string]$Username = "sa",
    [string]$Password = "YourPassword123",
    [switch]$UseWindowsAuth = $false
)

Write-Host "=== GIA Incident Management Platform - Database Setup ===" -ForegroundColor Cyan
Write-Host ""

# Load System.Data assembly
Add-Type -AssemblyName System.Data

$schemaFile = Join-Path $PSScriptRoot "database\schema.sql"

if (-not (Test-Path $schemaFile)) {
    Write-Host "ERROR: Schema file not found: $schemaFile" -ForegroundColor Red
    exit 1
}

try {
    # Build connection string
    if ($UseWindowsAuth) {
        $connectionString = "Server=$ServerInstance;Integrated Security=True;"
    } else {
        $connectionString = "Server=$ServerInstance;User Id=$Username;Password=$Password;"
    }
    
    Write-Host "Step 1: Connecting to SQL Server ($ServerInstance)..." -ForegroundColor Yellow
    
    # Connect to master database first
    $masterConn = New-Object System.Data.SqlClient.SqlConnection
    $masterConn.ConnectionString = $connectionString + "Database=master;"
    $masterConn.Open()
    
    Write-Host "[OK] Connected to SQL Server successfully" -ForegroundColor Green
    Write-Host ""
    
    # Check if database exists
    Write-Host "Step 2: Checking/Creating database '$Database'..." -ForegroundColor Yellow
    $checkDbCmd = $masterConn.CreateCommand()
    $checkDbCmd.CommandText = "SELECT name FROM sys.databases WHERE name = '$Database'"
    $dbExists = $checkDbCmd.ExecuteScalar()
    
    if ($null -eq $dbExists) {
        $createDbCmd = $masterConn.CreateCommand()
        $createDbCmd.CommandText = "CREATE DATABASE [$Database]"
        $createDbCmd.ExecuteNonQuery() | Out-Null
        Write-Host "[OK] Database '$Database' created successfully" -ForegroundColor Green
    } else {
        Write-Host "[OK] Database '$Database' already exists" -ForegroundColor Green
    }
    Write-Host ""
    
    $masterConn.Close()
    
    # Connect to the target database
    Write-Host "Step 3: Connecting to database '$Database'..." -ForegroundColor Yellow
    $conn = New-Object System.Data.SqlClient.SqlConnection
    $conn.ConnectionString = $connectionString + "Database=$Database;"
    $conn.Open()
    
    Write-Host "[OK] Connected to database '$Database'" -ForegroundColor Green
    Write-Host ""
    
    # Read schema file
    Write-Host "Step 4: Reading schema file..." -ForegroundColor Yellow
    $schema = Get-Content $schemaFile -Raw
    Write-Host "[OK] Schema file loaded" -ForegroundColor Green
    Write-Host ""
    
    # Remove comments
    $schema = $schema -replace '(?m)^\s*--.*$', ''
    $schema = $schema -replace '(?s)/\*.*?\*/', ''
    
    # Split by GO statements (case insensitive)
    Write-Host "Step 5: Executing schema..." -ForegroundColor Yellow
    $statements = $schema -split '(?i)\bGO\b' | Where-Object { $_.Trim().Length -gt 0 }
    
    $executed = 0
    $errors = @()
    
    foreach ($statement in $statements) {
        $statement = $statement.Trim()
        
        if ($statement.Length -lt 5) {
            continue
        }
        
        try {
            $cmd = $conn.CreateCommand()
            $cmd.CommandText = $statement
            $cmd.CommandTimeout = 30
            $cmd.ExecuteNonQuery() | Out-Null
            $executed++
            
            # Extract object name for feedback
            if ($statement -match '(?i)CREATE\s+(?:TABLE|INDEX|TRIGGER)\s+(?:dbo\.)?(\w+)') {
                $objectName = $matches[1]
                Write-Host "  [OK] Created: $objectName" -ForegroundColor Green
            }
            
        } catch {
            $errorMsg = "Error: $($_.Exception.Message)"
            $errors += $errorMsg
            Write-Host "  [ERROR] $errorMsg" -ForegroundColor Red
        }
    }
    
    Write-Host ""
    Write-Host "[OK] Executed $executed SQL statements" -ForegroundColor Green
    
    if ($errors.Count -gt 0) {
        Write-Host ""
        Write-Host "[WARNING] Warnings/Errors encountered:" -ForegroundColor Yellow
        foreach ($error in $errors) {
            Write-Host "  - $error" -ForegroundColor Yellow
        }
    }
    
    # Verify tables
    Write-Host ""
    Write-Host "Step 6: Verifying tables..." -ForegroundColor Yellow
    $tables = @('users', 'incidents', 'attachments', 'incident_logs')
    $allCreated = $true
    
    foreach ($table in $tables) {
        $checkCmd = $conn.CreateCommand()
        $checkCmd.CommandText = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'"
        $exists = $checkCmd.ExecuteScalar() -gt 0
        
        if ($exists) {
            Write-Host "  [OK] Table '$table' exists" -ForegroundColor Green
        } else {
            Write-Host "  [ERROR] Table '$table' NOT found" -ForegroundColor Red
            $allCreated = $false
        }
    }
    
    $conn.Close()
    
    Write-Host ""
    if ($allCreated) {
        Write-Host "=== Database setup completed successfully! ===" -ForegroundColor Green
        Write-Host "You can now use the application." -ForegroundColor Green
    } else {
        Write-Host "[WARNING] Some tables may be missing. Please check the errors above." -ForegroundColor Yellow
    }
    
} catch {
    Write-Host ""
    Write-Host "[ERROR] Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please check:" -ForegroundColor Yellow
    Write-Host "1. SQL Server is running"
    Write-Host "2. Connection parameters are correct"
    Write-Host "3. SQL Server allows SQL Authentication (if using -Username/-Password)"
    Write-Host "4. You have permissions to create databases"
    Write-Host ""
    Write-Host "Usage examples:" -ForegroundColor Cyan
    Write-Host "  .\setup_database.ps1 -ServerInstance 'localhost\SQLEXPRESS' -Username 'sa' -Password 'YourPassword'"
    Write-Host "  .\setup_database.ps1 -ServerInstance 'localhost' -UseWindowsAuth"
    exit 1
}
