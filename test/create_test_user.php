<?php
/**
 * Test User Creation Script
 * Run this AFTER database setup to create test users
 *
 * Usage: php create_test_user.php
 */

require_once __DIR__ . '/../includes/db.php';

echo "=== GIA Test User Creation ===\n\n";

/** Mot de passe unique pour tous les comptes de démonstration (identique à insert_test_data.php). */
$demoPassword = 'password';

try {
    $pdo = getDBConnection();

    if (!$pdo) {
        throw new Exception("Database connection failed. Please check db.php configuration.");
    }

    echo "[OK] Connected to database\n\n";

    $test_users = [
        ['username' => 'admin', 'email' => 'admin@naftal.dz', 'role' => 'Admin', 'department' => 'Groupe Informatique'],
        ['username' => 'tech1', 'email' => 'tech1@naftal.dz', 'role' => 'Technician', 'department' => 'Support IT'],
        ['username' => 'tech2', 'email' => 'tech2@naftal.dz', 'role' => 'Technician', 'department' => 'Support IT'],
        ['username' => 'reporter1', 'email' => 'reporter1@naftal.dz', 'role' => 'Reporter', 'department' => 'Branche Carburants'],
        ['username' => 'reporter2', 'email' => 'reporter2@naftal.dz', 'role' => 'Reporter', 'department' => 'Branche Carburants'],
    ];

    $created = 0;
    $skipped = 0;

    foreach ($test_users as $user_data) {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->execute([$user_data['username'], $user_data['email']]);

        if ($check_stmt->fetch()) {
            echo "[SKIP] User '{$user_data['username']}' already exists\n";
            $skipped++;
            continue;
        }

        $password_hash = password_hash($demoPassword, PASSWORD_DEFAULT);

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

        echo "[OK] Created user: {$user_data['username']} (Role: {$user_data['role']}, password: {$demoPassword})\n";
        $created++;
    }

    echo "\n=== Summary ===\n";
    echo "Created: $created users\n";
    echo "Skipped: $skipped users (already exist)\n\n";
    echo "All accounts use password: {$demoPassword}\n";
    echo "  admin, tech1, tech2, reporter1, reporter2\n";

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
