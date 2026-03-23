<?php
/**
 * Test User Creation Script
 * Run this AFTER database setup to create test users
 * 
 * Usage: php create_test_user.php
 */

require_once __DIR__ . '/../includes/db.php';

echo "=== GIA Test User Creation ===\n\n";

try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed. Please check db.php configuration.");
    }
    
    echo "[OK] Connected to database\n\n";
    
    // Test users with different roles
    $test_users = [
        [
            'username' => 'admin',
            'email' => 'admin@naftal.dz',
            'password' => 'admin123',
            'role' => 'Admin',
            'department' => 'Groupe Informatique'
        ],
        [
            'username' => 'tech1',
            'email' => 'tech1@naftal.dz',
            'password' => 'tech123',
            'role' => 'Technician',
            'department' => 'Support IT'
        ],
        [
            'username' => 'reporter1',
            'email' => 'reporter1@naftal.dz',
            'password' => 'user123',
            'role' => 'Reporter',
            'department' => 'Branche Carburants'
        ]
    ];
    
    $created = 0;
    $skipped = 0;
    
    foreach ($test_users as $user_data) {
        // Check if user already exists
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->execute([$user_data['username'], $user_data['email']]);
        
        if ($check_stmt->fetch()) {
            echo "[SKIP] User '{$user_data['username']}' already exists\n";
            $skipped++;
            continue;
        }
        
        // Hash password
        $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $insert_stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, department, created_at) 
            VALUES (?, ?, ?, ?, ?, GETDATE())
        ");
        
        $insert_stmt->execute([
            $user_data['username'],
            $user_data['email'],
            $password_hash,
            $user_data['role'],
            $user_data['department']
        ]);
        
        echo "[OK] Created user: {$user_data['username']} (Role: {$user_data['role']}, Password: {$user_data['password']})\n";
        $created++;
    }
    
    echo "\n=== Summary ===\n";
    echo "Created: $created users\n";
    echo "Skipped: $skipped users\n";
    echo "\nTest Credentials:\n";
    echo "Admin: admin / admin123\n";
    echo "Technician: tech1 / tech123\n";
    echo "Reporter: reporter1 / user123\n";
    
} catch (PDOException $e) {
    echo "\n[ERROR] Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. SQL Server is running\n";
    echo "2. Database 'GIA_IncidentDB' exists\n";
    echo "3. Tables are created (run setup_database.ps1 first)\n";
    exit(1);
} catch (Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
