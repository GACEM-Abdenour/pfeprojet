<?php
/**
 * Database Connection File for SQL Server Express
 * Uses PDO with SQLSRV driver
 * 
 * Configuration: Update these values according to your SQL Server setup
 */

// Database configuration
// Based on your SQL Server setup: SQLEXPRESS instance with Windows Authentication
define('DB_HOST', 'localhost\SQLEXPRESS'); // SQL Server instance name (or just 'SQLEXPRESS')
define('DB_NAME', 'GIA_IncidentDB'); // Database name
define('DB_USER', ''); // Empty for Windows Authentication
define('DB_PASS', ''); // Empty for Windows Authentication
define('DB_CHARSET', 'UTF-8');
define('DB_USE_WINDOWS_AUTH', true); // Use Windows Authentication (Integrated Security=True)

/**
 * Get PDO Database Connection
 * 
 * @return PDO|null Returns PDO connection object or null on failure
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // Check if PDO_SQLSRV extension is loaded
            if (!extension_loaded('pdo_sqlsrv')) {
                throw new Exception("PDO_SQLSRV extension is not loaded. Please install Microsoft Drivers for PHP for SQL Server.");
            }

            // SQL Server connection string for PDO
            // NOTE: PDO_SQLSRV does NOT support 'CharacterSet' in the DSN string.
            // Character set is controlled via PDO::SQLSRV_ATTR_ENCODING instead.
            $dsn = "sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME;

            // Connection options
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false // Use native prepared statements
            ];

            // Set encoding to UTF-8 if the constant is available
            if (defined('PDO::SQLSRV_ATTR_ENCODING') && defined('PDO::SQLSRV_ENCODING_UTF8')) {
                $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
            }

            // Use Windows Authentication if configured
            if (defined('DB_USE_WINDOWS_AUTH') && DB_USE_WINDOWS_AUTH) {
                $pdo = new PDO($dsn, null, null, $options);
            } else {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }

        } catch (PDOException $e) {
            // Log error securely (don't expose sensitive info in production)
            error_log("Database Connection Error: " . $e->getMessage());

            // In production, show generic error message
            die("Database connection failed. Please contact the administrator.");
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * Useful for debugging
 * 
 * @return bool True if connection successful, false otherwise
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            // Simple query to test connection
            $stmt = $pdo->query("SELECT @@VERSION AS SQLServerVersion");
            $result = $stmt->fetch();
            return !empty($result);
        }
        return false;
    } catch (PDOException $e) {
        error_log("Database Test Error: " . $e->getMessage());
        return false;
    }
}

// Uncomment the line below to test connection when accessing this file directly
// testDBConnection();
