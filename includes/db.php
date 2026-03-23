<?php
/**
 * Database Connection File for SQL Server Express
 * Uses PDO with SQLSRV driver
 *
 * Configuration: includes/config.php (or config.example.php)
 */

if (is_readable(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    require_once __DIR__ . '/config.example.php';
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'UTF-8');
}
if (!defined('DB_USE_WINDOWS_AUTH')) {
    define('DB_USE_WINDOWS_AUTH', true);
}

/**
 * Get PDO Database Connection
 *
 * @return PDO|null Returns PDO connection object or null on failure
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            if (!extension_loaded('pdo_sqlsrv')) {
                throw new Exception("PDO_SQLSRV extension is not loaded. Please install Microsoft Drivers for PHP for SQL Server.");
            }

            $dsn = 'sqlsrv:Server=' . DB_SERVER . ';Database=' . DB_NAME;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            if (defined('PDO::SQLSRV_ATTR_ENCODING') && defined('PDO::SQLSRV_ENCODING_UTF8')) {
                $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
            }

            if (defined('DB_USE_WINDOWS_AUTH') && DB_USE_WINDOWS_AUTH) {
                $pdo = new PDO($dsn, null, null, $options);
            } else {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please contact the administrator.');
        }
    }

    return $pdo;
}

/**
 * Test database connection
 *
 * @return bool True if connection successful, false otherwise
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $stmt = $pdo->query('SELECT @@VERSION AS SQLServerVersion');
            $result = $stmt->fetch();
            return !empty($result);
        }
        return false;
    } catch (PDOException $e) {
        error_log('Database Test Error: ' . $e->getMessage());
        return false;
    }
}
