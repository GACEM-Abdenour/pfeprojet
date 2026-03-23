# Quick SQL Server Checker Script
# Checks if SQL Server is installed and running

Write-Host "=== SQL Server Status Check ===" -ForegroundColor Cyan
Write-Host ""

# Check for SQL Server services
$sqlServices = Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}

if ($sqlServices) {
    Write-Host "[OK] SQL Server services found:" -ForegroundColor Green
    Write-Host ""
    
    foreach ($service in $sqlServices) {
        $statusColor = if ($service.Status -eq 'Running') { "Green" } else { "Yellow" }
        $statusIcon = if ($service.Status -eq 'Running') { "[RUNNING]" } else { "[STOPPED]" }
        
        Write-Host "  Service: $($service.DisplayName)" -ForegroundColor White
        Write-Host "  Status:  $statusIcon" -ForegroundColor $statusColor
        Write-Host "  Name:    $($service.Name)" -ForegroundColor Gray
        Write-Host ""
    }
    
    # Determine instance name
    $runningServices = $sqlServices | Where-Object {$_.Status -eq 'Running'}
    
    if ($runningServices) {
        Write-Host "[OK] SQL Server is running!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Suggested command:" -ForegroundColor Cyan
        
        $defaultInstance = $runningServices | Where-Object {$_.Name -eq "MSSQLSERVER"}
        $expressInstance = $runningServices | Where-Object {$_.Name -like "MSSQL$SQLEXPRESS"}
        
        if ($defaultInstance) {
            Write-Host "  .\setup_database.ps1 -ServerInstance `"localhost`" -UseWindowsAuth" -ForegroundColor Yellow
        } elseif ($expressInstance) {
            Write-Host "  .\setup_database.ps1 -ServerInstance `"localhost\SQLEXPRESS`" -UseWindowsAuth" -ForegroundColor Yellow
        } else {
            $firstService = $runningServices[0]
            $instanceName = $firstService.Name -replace "MSSQL\$", ""
            if ($instanceName -eq "MSSQLSERVER") {
                Write-Host "  .\setup_database.ps1 -ServerInstance `"localhost`" -UseWindowsAuth" -ForegroundColor Yellow
            } else {
                Write-Host "  .\setup_database.ps1 -ServerInstance `"localhost\$instanceName`" -UseWindowsAuth" -ForegroundColor Yellow
            }
        }
    } else {
        Write-Host "[WARNING] SQL Server is installed but NOT running" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "To start SQL Server:" -ForegroundColor Cyan
        
        $stoppedService = $sqlServices | Where-Object {$_.Status -eq 'Stopped'} | Select-Object -First 1
        if ($stoppedService) {
            Write-Host "  Start-Service `"$($stoppedService.Name)`"" -ForegroundColor Yellow
        }
    }
    
} else {
    Write-Host "[ERROR] SQL Server is NOT installed" -ForegroundColor Red
    Write-Host ""
    Write-Host "To install SQL Server Express:" -ForegroundColor Cyan
    Write-Host "1. Download from: https://www.microsoft.com/en-us/sql-server/sql-server-downloads" -ForegroundColor White
    Write-Host "2. Choose 'Express' edition (Free)" -ForegroundColor White
    Write-Host "3. Install with 'Basic' installation" -ForegroundColor White
    Write-Host "4. Note the instance name (usually SQLEXPRESS)" -ForegroundColor White
    Write-Host ""
    Write-Host "See SQL_SERVER_SETUP.md for detailed instructions" -ForegroundColor Cyan
}

Write-Host ""
