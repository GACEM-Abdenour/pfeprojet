<?php
/**
 * Code Validation Script
 * Validates PHP syntax and checks for common issues
 */

echo "=== GIA Code Validation ===\n\n";

$files_to_check = [
    '../includes/db.php',
    '../includes/auth.php',
    '../includes/functions.php',
    '../includes/logout.php',
    '../pages/login.php',
    '../index.php'
];

$errors = 0;
$warnings = 0;

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    
    if (!file_exists($full_path)) {
        echo "[ERROR] File not found: $file\n";
        $errors++;
        continue;
    }
    
    echo "Checking: $file ... ";
    
    // Check PHP syntax
    $output = [];
    $return_var = 0;
    exec("php -l \"$full_path\" 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "[OK]\n";
    } else {
        echo "[ERROR]\n";
        foreach ($output as $line) {
            echo "  $line\n";
        }
        $errors++;
    }
    
    // Check for common security issues
    $content = file_get_contents($full_path);
    
    // Check for direct SQL without prepared statements (basic check)
    if (preg_match('/\$.*->(query|exec)\(["\'].*\$/', $content)) {
        echo "  [WARNING] Possible SQL injection risk - check for prepared statements\n";
        $warnings++;
    }
    
    // Check for password_hash usage in auth.php
    if (basename($file) === 'auth.php' && !strpos($content, 'password_verify')) {
        echo "  [WARNING] auth.php should use password_verify()\n";
        $warnings++;
    }
}

echo "\n=== Summary ===\n";
echo "Errors: $errors\n";
echo "Warnings: $warnings\n";

if ($errors === 0) {
    echo "\n[OK] All files passed syntax validation!\n";
    exit(0);
} else {
    echo "\n[ERROR] Please fix the errors above.\n";
    exit(1);
}
