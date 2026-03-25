<?php
/**
 * Database configuration defaults.
 * Copy this file to config.php and adjust for your environment.
 * (config.php is git-ignored for local/production overrides.)
 *
 * DB_USE_WINDOWS_AUTH : true = compte Windows (développement local Windows + SQL Server).
 *                       false = DB_USER / DB_PASS (Docker, Linux, ou authentification SQL).
 * DB_TRUST_SERVER_CERT : true souvent requis pour SQL Server en conteneur (certificat auto-signé).
 */
define('DB_SERVER', 'localhost\\SQLEXPRESS');
define('DB_NAME', 'GIA_IncidentDB');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_USE_WINDOWS_AUTH', true);
define('DB_TRUST_SERVER_CERT', false);
