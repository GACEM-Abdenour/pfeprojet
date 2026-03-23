# PowerShell script to run SQL test data insertion
# Uses sqlcmd or .NET SqlClient

param(
    [string]$ServerInstance = "localhost\SQLEXPRESS",
    [string]$Database = "GIA_IncidentDB",
    [switch]$UseWindowsAuth = $true
)

Write-Host "=== Running Test Data SQL Script ===" -ForegroundColor Cyan
Write-Host ""

$sqlFile = Join-Path $PSScriptRoot "test\insert_test_data.sql"

if (-not (Test-Path $sqlFile)) {
    Write-Host "[ERROR] SQL file not found: $sqlFile" -ForegroundColor Red
    exit 1
}

# Try using sqlcmd first
$sqlcmd = Get-Command sqlcmd -ErrorAction SilentlyContinue

if ($sqlcmd) {
    Write-Host "Using sqlcmd..." -ForegroundColor Yellow
    
    if ($UseWindowsAuth) {
        $cmd = "sqlcmd -S $ServerInstance -d $Database -E -i `"$sqlFile`""
    } else {
        Write-Host "[ERROR] SQL Authentication not supported with sqlcmd in this script" -ForegroundColor Red
        exit 1
    }
    
    Invoke-Expression $cmd
    
} else {
    # Use .NET SqlClient
    Write-Host "Using .NET SqlClient..." -ForegroundColor Yellow
    
    Add-Type -AssemblyName System.Data
    
    try {
        if ($UseWindowsAuth) {
            $connectionString = "Server=$ServerInstance;Database=$Database;Integrated Security=True;"
        } else {
            Write-Host "[ERROR] SQL Authentication not implemented" -ForegroundColor Red
            exit 1
        }
        
        $conn = New-Object System.Data.SqlClient.SqlConnection
        $conn.ConnectionString = $connectionString
        $conn.Open()
        
        Write-Host "[OK] Connected to database" -ForegroundColor Green
        Write-Host ""
        
        # Read SQL file
        $sql = Get-Content $sqlFile -Raw
        
        # Split by GO statements
        $statements = $sql -split '(?i)\bGO\b' | Where-Object { $_.Trim().Length -gt 0 }
        
        foreach ($statement in $statements) {
            $statement = $statement.Trim()
            if ($statement.Length -lt 5) { continue }
            
            try {
                $cmd = $conn.CreateCommand()
                $cmd.CommandText = $statement
                $cmd.CommandTimeout = 30
                $result = $cmd.ExecuteNonQuery()
                
                # Check for PRINT statements in output
                if ($statement -match "PRINT") {
                    # PRINT statements don't return through ExecuteNonQuery
                    # We'll just continue
                }
            } catch {
                # Some errors are expected (like "already exists")
                if ($_.Exception.Message -notmatch "already exists" -and 
                    $_.Exception.Message -notmatch "already exists on table") {
                    Write-Host "  [WARNING] $($_.Exception.Message)" -ForegroundColor Yellow
                }
            }
        }
        
        $conn.Close()
        Write-Host ""
        Write-Host "[OK] Test data insertion completed!" -ForegroundColor Green
        
    } catch {
        Write-Host ""
        Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "Test credentials:" -ForegroundColor Cyan
Write-Host "  Admin: admin / admin123" -ForegroundColor White
Write-Host "  Technician: tech1 / tech123" -ForegroundColor White
Write-Host "  Reporter: reporter1 / user123" -ForegroundColor White
Write-Host ""
Write-Host "NOTE: Passwords use test hash. For production login, use PHP password_hash()" -ForegroundColor Yellow
