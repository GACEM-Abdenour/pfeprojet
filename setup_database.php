<?php
/**
 * Database Setup Script
 * Creates the database and runs the schema.sql file
 * 
 * Usage: php setup_database.php
 */

require_once __DIR__ . '/includes/db.php';

// Database configuration (same as db.php but without DB_NAME for initial connection)
$dbName = DB_NAME;

echo "=== GIA Incident Management Platform - Database Setup ===\n\n";

try {
    // Step 1: Connect to SQL Server without specifying a database
    echo "Step 1: Connecting to SQL Server...\n";
    $pdo = gia_pdo_connect(null);
    echo "✓ Connected to SQL Server successfully\n\n";
    
    // Step 2: Create database if it doesn't exist
    echo "Step 2: Checking/Creating database '{$dbName}'...\n";
    $checkDb = $pdo->query("SELECT name FROM sys.databases WHERE name = '$dbName'");
    if ($checkDb->rowCount() == 0) {
        $pdo->exec("CREATE DATABASE [$dbName]");
        echo "✓ Database '{$dbName}' created successfully\n\n";
    } else {
        echo "✓ Database '{$dbName}' already exists\n\n";
    }
    
    // Step 3: Connect to the specific database
    echo "Step 3: Connecting to database '{$dbName}'...\n";
    $pdo = gia_pdo_connect($dbName);
    echo "✓ Connected to database '{$dbName}'\n\n";
    
    // Step 4: Read and execute schema.sql
    echo "Step 4: Reading schema file...\n";
    $schemaFile = __DIR__ . '/database/schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }
    
    $schema = file_get_contents($schemaFile);
    echo "✓ Schema file loaded\n\n";
    
    // Step 5: Split SQL by GO statements and execute
    echo "Step 5: Executing schema...\n";
    
    // Remove comments and split by GO
    $schema = preg_replace('/--.*$/m', '', $schema); // Remove single-line comments
    $schema = preg_replace('/\/\*.*?\*\//s', '', $schema); // Remove multi-line comments
    
    // Split by GO statements (case insensitive)
    $statements = preg_split('/\bGO\b/i', $schema);
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        // Skip empty statements
        if (empty($statement) || strlen($statement) < 5) {
            continue;
        }
        
        try {
            // Execute the statement
            $pdo->exec($statement);
            $executed++;
            
            // Extract table/object name for feedback
            if (preg_match('/CREATE\s+(?:TABLE|INDEX|TRIGGER)\s+(?:dbo\.)?(\w+)/i', $statement, $matches)) {
                $objectName = $matches[1];
                echo "  ✓ Created: {$objectName}\n";
            } elseif (preg_match('/DROP\s+(?:TABLE|TRIGGER)\s+(?:dbo\.)?(\w+)/i', $statement, $matches)) {
                // Silent drop (expected behavior)
            }
            
        } catch (PDOException $e) {
            $errorMsg = "Error executing statement " . ($index + 1) . ": " . $e->getMessage();
            $errors[] = $errorMsg;
            echo "  ✗ Error: " . substr($errorMsg, 0, 100) . "...\n";
        }
    }
    
    echo "\n✓ Executed {$executed} SQL statements\n";
    
    if (!empty($errors)) {
        echo "\n⚠ Warnings/Errors encountered:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }
    
    // Step 6: Verify tables were created
    echo "\nStep 6: Verifying tables...\n";
    $tables = ['users', 'incidents', 'attachments', 'incident_logs'];
    $allCreated = true;
    
    foreach ($tables as $table) {
        $check = $pdo->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
        $exists = $check->fetch()['cnt'] > 0;
        
        if ($exists) {
            echo "  ✓ Table '{$table}' exists\n";
        } else {
            echo "  ✗ Table '{$table}' NOT found\n";
            $allCreated = false;
        }
    }
    
    if ($allCreated) {
        echo "\n=== Database setup completed successfully! ===\n";
        echo "You can now use the application.\n";
    } else {
        echo "\n⚠ Some tables may be missing. Please check the errors above.\n";
    }
    
} catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. SQL Server is running\n";
    echo "2. Connection credentials in includes/config.php are correct\n";
    echo "3. SQL Server allows SQL Authentication (if using sa account)\n";
    echo "4. PDO_SQLSRV extension is installed in PHP\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
