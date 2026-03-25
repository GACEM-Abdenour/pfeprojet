<?php
/**
 * Configuration pour Docker (SQL Server Linux + authentification SQL).
 * Copié vers config.php lors du build Docker — ne pas utiliser en production Windows
 * sans adaptation.
 *
 * Variables d'environnement (docker-compose) :
 *   DB_SERVER (défaut : db)
 *   DB_NAME
 *   DB_USER (défaut : sa)
 *   DB_PASS ou MSSQL_SA_PASSWORD
 */
define('DB_SERVER', getenv('DB_SERVER') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'GIA_IncidentDB');

$dbPass = getenv('DB_PASS');
if ($dbPass === false || $dbPass === '') {
    $dbPass = getenv('MSSQL_SA_PASSWORD') ?: '';
}
define('DB_USER', getenv('DB_USER') !== false && getenv('DB_USER') !== '' ? getenv('DB_USER') : 'sa');
define('DB_PASS', $dbPass);

define('DB_USE_WINDOWS_AUTH', false);
define('DB_TRUST_SERVER_CERT', filter_var(getenv('DB_TRUST_SERVER_CERT') ?: 'true', FILTER_VALIDATE_BOOLEAN));
