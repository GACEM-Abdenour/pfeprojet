# Comprehensive Test Runner for GIA Login System
# Tests code structure, file existence, and provides setup instructions

Write-Host "=== GIA Login System - Test Runner ===" -ForegroundColor Cyan
Write-Host ""

$errors = 0
$warnings = 0
$passed = 0

# Test 1: Check required files exist
Write-Host "Test 1: Checking required files..." -ForegroundColor Yellow
$required_files = @(
    "includes\db.php",
    "includes\auth.php",
    "includes\functions.php",
    "includes\logout.php",
    "pages\login.php",
    "index.php",
    "database\schema.sql"
)

foreach ($file in $required_files) {
    $full_path = Join-Path $PSScriptRoot $file
    if (Test-Path $full_path) {
        Write-Host "  [OK] $file exists" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [ERROR] $file NOT FOUND" -ForegroundColor Red
        $errors++
    }
}

# Test 2: Check for security patterns
Write-Host ""
Write-Host "Test 2: Checking security patterns..." -ForegroundColor Yellow

$auth_file = Join-Path $PSScriptRoot "includes\auth.php"
if (Test-Path $auth_file) {
    $content = Get-Content $auth_file -Raw
    
    if ($content -match 'password_verify') {
        Write-Host "  [OK] password_verify() found" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [WARNING] password_verify() not found" -ForegroundColor Yellow
        $warnings++
    }
    
    if ($content -match 'prepare\(') {
        Write-Host "  [OK] Prepared statements found" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [WARNING] Prepared statements not found" -ForegroundColor Yellow
        $warnings++
    }
    
    if ($content -match 'session_regenerate_id') {
        Write-Host "  [OK] Session regeneration found" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [WARNING] Session regeneration not found" -ForegroundColor Yellow
        $warnings++
    }
}

# Test 3: Check schema.sql for IDENTITY fix
Write-Host ""
Write-Host "Test 3: Checking schema.sql..." -ForegroundColor Yellow
$schema_file = Join-Path $PSScriptRoot "database\schema.sql"
if (Test-Path $schema_file) {
    $content = Get-Content $schema_file -Raw
    
    if ($content -match 'CREATE TABLE.*incident_logs' -and $content -match 'id INT IDENTITY\(1,1\)') {
        Write-Host "  [OK] incident_logs.id has IDENTITY(1,1)" -ForegroundColor Green
        $passed++
    } elseif ($content -match 'CREATE TABLE.*incident_logs' -and $content -match 'id INT.*PRIMARY KEY' -and -not ($content -match 'id INT IDENTITY')) {
        Write-Host "  [ERROR] incident_logs.id missing IDENTITY(1,1)" -ForegroundColor Red
        $errors++
    } else {
        Write-Host "  [OK] incident_logs.id has IDENTITY(1,1)" -ForegroundColor Green
        $passed++
    }
}

# Test 4: Check login.php structure
Write-Host ""
Write-Host "Test 4: Checking login.php structure..." -ForegroundColor Yellow
$login_file = Join-Path $PSScriptRoot "pages\login.php"
if (Test-Path $login_file) {
    $content = Get-Content $login_file -Raw
    
    if ($content -match 'auth\.php') {
        Write-Host "  [OK] Form posts to auth.php" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [ERROR] Form does not post to auth.php" -ForegroundColor Red
        $errors++
    }
    
    if ($content -match 'Nom d''utilisateur') {
        Write-Host "  [OK] French labels found" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  [WARNING] French labels not found" -ForegroundColor Yellow
        $warnings++
    }
}

# Test 5: Check for SQL Server availability
Write-Host ""
Write-Host "Test 5: Checking SQL Server availability..." -ForegroundColor Yellow
$sql_services = Get-Service | Where-Object {$_.DisplayName -like "*SQL*"}
if ($sql_services) {
    Write-Host "  [INFO] SQL Server services found:" -ForegroundColor Cyan
    foreach ($service in $sql_services) {
        $status = if ($service.Status -eq 'Running') { "[RUNNING]" } else { "[STOPPED]" }
        Write-Host "    - $($service.DisplayName) $status" -ForegroundColor $(if ($service.Status -eq 'Running') { "Green" } else { "Yellow" })
    }
} else {
    Write-Host "  [WARNING] No SQL Server services found" -ForegroundColor Yellow
    Write-Host "    Install SQL Server Express to enable database functionality" -ForegroundColor Yellow
    $warnings++
}

# Test 6: Check PHP availability
Write-Host ""
Write-Host "Test 6: Checking PHP availability..." -ForegroundColor Yellow
$php_paths = @(
    "C:\php\php.exe",
    "C:\xampp\php\php.exe",
    "C:\wamp\bin\php\php.exe",
    "C:\Program Files\PHP\php.exe"
)

$php_found = $false
foreach ($path in $php_paths) {
    if (Test-Path $path) {
        Write-Host "  [OK] PHP found at: $path" -ForegroundColor Green
        $php_found = $true
        $passed++
        break
    }
}

if (-not $php_found) {
    Write-Host "  [WARNING] PHP not found in common locations" -ForegroundColor Yellow
    Write-Host "    Install PHP with PDO_SQLSRV extension to run the application" -ForegroundColor Yellow
    $warnings++
}

# Summary
Write-Host ""
Write-Host "=== Test Summary ===" -ForegroundColor Cyan
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Warnings: $warnings" -ForegroundColor Yellow
Write-Host "Errors: $errors" -ForegroundColor $(if ($errors -eq 0) { "Green" } else { "Red" })
Write-Host ""

if ($errors -eq 0) {
    Write-Host "[OK] Code structure is valid!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Install SQL Server Express (if not installed)" -ForegroundColor White
    Write-Host "2. Update includes/db.php with SQL Server credentials" -ForegroundColor White
    Write-Host "3. Run: .\setup_database.ps1" -ForegroundColor White
    Write-Host "4. Run: php test\create_test_user.php" -ForegroundColor White
    Write-Host "5. Access: pages/login.php in your web browser" -ForegroundColor White
} else {
    Write-Host "[ERROR] Please fix the errors above before proceeding" -ForegroundColor Red
}

Write-Host ""
Write-Host "Demo page available at: demo\demo_login.html" -ForegroundColor Cyan
