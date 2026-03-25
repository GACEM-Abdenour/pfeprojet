<?php
/**
 * Attend que SQL Server accepte une connexion (utilisé par entrypoint Docker).
 */
$server = getenv('DB_SERVER') ?: 'db';
$user = getenv('DB_USER') ?: 'sa';
$pass = getenv('DB_PASS');
if ($pass === false || $pass === '') {
    $pass = getenv('MSSQL_SA_PASSWORD') ?: '';
}

$dsn = 'sqlsrv:Server=' . $server . ';TrustServerCertificate=1';

for ($i = 1; $i <= 90; $i++) {
    try {
        new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        fwrite(STDERR, "SQL Server OK après tentative $i\n");
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "Attente SQL Server ($i/90)...\n");
        sleep(2);
    }
}

fwrite(STDERR, "Timeout: SQL Server injoignable.\n");
exit(1);
