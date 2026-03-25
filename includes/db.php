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
if (!defined('DB_TRUST_SERVER_CERT')) {
    define('DB_TRUST_SERVER_CERT', false);
}

/**
 * Build PDO DSN for sqlsrv (optionally without database, for setup scripts).
 *
 * @param string|null $database Database name or null/empty for server-only connection
 */
function gia_build_dsn($database = null) {
    $dsn = 'sqlsrv:Server=' . DB_SERVER;
    if ($database !== null && $database !== '') {
        $dsn .= ';Database=' . $database;
    }
    if (defined('DB_TRUST_SERVER_CERT') && DB_TRUST_SERVER_CERT) {
        $dsn .= ';TrustServerCertificate=1';
    }
    return $dsn;
}

/**
 * PDO connection options shared by the app and setup scripts.
 */
function gia_pdo_options() {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (defined('PDO::SQLSRV_ATTR_ENCODING') && defined('PDO::SQLSRV_ENCODING_UTF8')) {
        $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
    }
    return $options;
}

/**
 * Create a new PDO connection (used by setup_database.php and getDBConnection).
 *
 * @param string|null $database Pass DB_NAME for app; null for master connection during setup
 */
function gia_pdo_connect($database = null) {
    if (!extension_loaded('pdo_sqlsrv')) {
        throw new Exception('PDO_SQLSRV extension is not loaded. Please install Microsoft Drivers for PHP for SQL Server.');
    }
    $dsn = gia_build_dsn($database);
    $options = gia_pdo_options();

    if (defined('DB_USE_WINDOWS_AUTH') && DB_USE_WINDOWS_AUTH) {
        return new PDO($dsn, null, null, $options);
    }
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

/**
 * Get PDO Database Connection
 *
 * @return PDO Returns PDO connection object
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = gia_pdo_connect(DB_NAME);
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
