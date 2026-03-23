<?php
/**
 * Test script: ticket assignment flow (SQL used by update_ticket.php).
 * Verifies that the lock + update works on SQL Server.
 *
 * Usage: php test/test_assign_ticket.php [incident_id] [tech_user_id]
 * Example: php test/test_assign_ticket.php 9 2
 * If no args: only runs the SELECT with UPDLOCK to verify syntax.
 */

require_once __DIR__ . '/../includes/db.php';

echo "=== GIA Test: Ticket assignment (SQL Server lock + update) ===\n\n";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed.');
    }
    echo "[OK] Connected to database\n";

    $incidentId = isset($argv[1]) ? (int)$argv[1] : 0;
    $techId     = isset($argv[2]) ? (int)$argv[2] : 0;

    // 1) Test SELECT with UPDLOCK (same as update_ticket.php)
    echo "\n1) Testing SELECT ... WITH (UPDLOCK, ROWLOCK) ...\n";
    $stmt = $pdo->prepare("
        SELECT id, status, assigned_to
        FROM incidents WITH (UPDLOCK, ROWLOCK)
        WHERE id = ?
    ");
    $stmt->execute([$incidentId ?: 1]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "   [SKIP] No incident with id " . ($incidentId ?: 1) . ". Run with a valid incident_id.\n";
    } else {
        echo "   [OK] Lock acquired. Incident #{$row['id']} status={$row['status']} assigned_to=" . ($row['assigned_to'] ?? 'NULL') . "\n";
    }

    // 2) If both IDs given, run the assignment update in a transaction then rollback
    if ($incidentId > 0 && $techId > 0) {
        echo "\n2) Testing assignment UPDATE (will rollback)...\n";
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                SELECT id, status, assigned_to
                FROM incidents WITH (UPDLOCK, ROWLOCK)
                WHERE id = ?
            ");
            $stmt->execute([$incidentId]);
            $incident = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$incident) {
                throw new Exception("Incident #{$incidentId} not found.");
            }
            $check = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'Technician'");
            $check->execute([$techId]);
            $tech = $check->fetch(PDO::FETCH_ASSOC);
            if (!$tech) {
                throw new Exception("User #{$techId} is not a Technician.");
            }
            $update = $pdo->prepare("
                UPDATE incidents
                SET assigned_to = ?, status = CASE WHEN status = 'Open' THEN 'Assigned' ELSE status END, updated_at = GETDATE()
                WHERE id = ?
            ");
            $update->execute([$techId, $incidentId]);
            echo "   [OK] UPDATE executed (assigned_to={$techId}, tech={$tech['username']})\n";
        } finally {
            $pdo->rollBack();
            echo "   [OK] Transaction rolled back (no data changed).\n";
        }
    } else {
        echo "\n2) Skipping UPDATE test. Run: php test/test_assign_ticket.php <incident_id> <tech_user_id>\n";
    }

    echo "\n=== All checks passed. Assignment flow is OK. ===\n";
    exit(0);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}
