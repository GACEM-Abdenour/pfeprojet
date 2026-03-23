<?php
/**
 * Update Ticket Action - handles "take ticket" and status updates.
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/tech_dashboard.php?error=' . urlencode('Méthode non autorisée'));
    exit;
}

requireLogin();

$role          = getCurrentUserRole();
$userId        = getCurrentUserId();
$incidentId    = isset($_POST['incident_id']) ? (int)$_POST['incident_id'] : 0;
$action        = isset($_POST['action']) ? trim($_POST['action']) : '';
$newStatus     = isset($_POST['status']) ? trim($_POST['status']) : '';
$comment       = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$assignedToRaw = isset($_POST['assigned_to']) ? $_POST['assigned_to'] : '';
$assignedToId  = $assignedToRaw === '' || $assignedToRaw === '0' ? 0 : (int)$assignedToRaw;
$assignComment = isset($_POST['assign_comment']) ? trim($_POST['assign_comment']) : '';

if ($incidentId <= 0) {
    header('Location: ../pages/tech_dashboard.php?error=' . urlencode('Ticket invalide'));
    exit;
}

if (!$userId) {
    header('Location: ../pages/login.php?error=' . urlencode('Session expirée, veuillez vous reconnecter'));
    exit;
}

$allowedStatuses = ['Open', 'Assigned', 'Diagnostic', 'Resolved', 'Closed', 'Failed/Blocked'];

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Lock incident row (SQL Server: use UPDLOCK/ROWLOCK instead of FOR UPDATE)
    $stmt = $pdo->prepare("
        SELECT id, status, assigned_to
        FROM incidents WITH (UPDLOCK, ROWLOCK)
        WHERE id = ?
    ");
    $stmt->execute([$incidentId]);
    $incident = $stmt->fetch();

    if (!$incident) {
        throw new Exception('Ticket introuvable');
    }

    $currentStatus   = $incident['status'];
    $currentAssigned = $incident['assigned_to'];

    if ($action === 'assign_tech') {
        // Admin only: assign ticket to a technician from dropdown (or unassign if 0)
        if ($role !== 'Admin') {
            throw new Exception('Seuls les administrateurs peuvent assigner un ticket.');
        }
        if ($assignedToRaw === '') {
            throw new Exception('Veuillez sélectionner un technicien ou « Non assigné ».');
        }
        if ($assignedToRaw === '0' || $assignedToId === 0) {
            $update = $pdo->prepare("
                UPDATE incidents
                SET assigned_to = NULL, updated_at = GETDATE()
                WHERE id = ?
            ");
            $update->execute([$incidentId]);
            $msg = 'Ticket désassigné par l\'administrateur';
            if ($assignComment !== '') {
                $msg .= '. ' . $assignComment;
            }
            logIncidentAction($incidentId, $userId, 'Assignment', $msg);
            $pdo->commit();
            header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&success=' . urlencode('Ticket désassigné'));
            exit;
        }
        $check = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'Technician'");
        $check->execute([$assignedToId]);
        $tech = $check->fetch();
        if (!$tech) {
            throw new Exception('Technicien invalide.');
        }
        $update = $pdo->prepare("
            UPDATE incidents
            SET assigned_to = ?, status = CASE WHEN status = 'Open' THEN 'Assigned' ELSE status END, updated_at = GETDATE()
            WHERE id = ?
        ");
        $update->execute([$assignedToId, $incidentId]);
        $msg = 'Ticket assigné à ' . $tech['username'] . ' par l\'administrateur';
        if ($assignComment !== '') {
            $msg .= '. ' . $assignComment;
        }
        logIncidentAction($incidentId, $userId, 'Assignment', $msg);
        $pdo->commit();
        header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&success=' . urlencode('Ticket assigné à ' . $tech['username']));
        exit;
    }

    if ($action === 'take_ticket') {
        // Only technicians can take tickets
        if ($role !== 'Technician') {
            throw new Exception("Seuls les techniciens peuvent s'assigner un ticket.");
        }

        // Only if currently unassigned
        if ($currentAssigned !== null) {
            throw new Exception('Ce ticket est déjà assigné.');
        }

        $update = $pdo->prepare("
            UPDATE incidents
            SET assigned_to = ?, status = CASE WHEN status = 'Open' THEN 'Assigned' ELSE status END, updated_at = GETDATE()
            WHERE id = ?
        ");
        $update->execute([$userId, $incidentId]);

        logIncidentAction($incidentId, $userId, 'Assignment', "Ticket pris en charge");

        $pdo->commit();
        header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&success=' . urlencode('Ticket pris en charge'));
        exit;
    }

    if ($action === 'update_status') {
        // Only technician (own ticket) or admin can change status
        if (!in_array($role, ['Technician', 'Admin'], true)) {
            throw new Exception('Rôle non autorisé pour la mise à jour du statut.');
        }

        if ($newStatus !== '' && !in_array($newStatus, $allowedStatuses, true)) {
            throw new Exception('Statut invalide.');
        }

        // If technician, enforce ownership
        if ($role === 'Technician') {
            if ($currentAssigned === null || (int)$currentAssigned !== (int)$userId) {
                throw new Exception("Vous ne pouvez mettre à jour que vos propres tickets.");
            }
        }

        $statusToSet = $currentStatus;
        if ($newStatus !== '') {
            $statusToSet = $newStatus;
        }

        // Technician: comment is mandatory when setting Resolved or Failed/Blocked
        if ($role === 'Technician' && in_array($statusToSet, ['Resolved', 'Failed/Blocked'], true)) {
            if ($comment === '') {
                $pdo->rollBack();
                header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&error=' . urlencode('Un commentaire ou rapport est obligatoire pour les statuts « Résolu » et « Échec / Bloqué ».'));
                exit;
            }
        }

        // When assigned tech sets status to Open, return ticket to unassigned pool
        $unassignMe = false;
        if ($role === 'Technician' && $statusToSet === 'Open' && (int)$currentAssigned === (int)$userId) {
            $unassignMe = true;
        }

        $params = [$statusToSet];
        $sql = "UPDATE incidents SET status = ?, updated_at = GETDATE()";
        if ($unassignMe) {
            $sql .= ", assigned_to = NULL";
        }
        if ($statusToSet === 'Closed') {
            $sql .= ", closed_at = GETDATE()";
        }
        $sql .= " WHERE id = ?";
        $params[] = $incidentId;

        $update = $pdo->prepare($sql);
        $update->execute($params);

        if ($statusToSet !== $currentStatus) {
            $message = "Statut changé de '{$currentStatus}' à '{$statusToSet}'";
            if ($unassignMe) {
                $message .= " — ticket remis en file non assignée";
            }
            logIncidentAction($incidentId, $userId, 'Status Change', $message);
        }

        if ($comment !== '') {
            logIncidentAction($incidentId, $userId, 'Comment', $comment);
        }

        $pdo->commit();
        header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&success=' . urlencode('Ticket mis à jour avec succès'));
        exit;
    }

    // Unknown action
    throw new Exception('Action invalide.');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Update Ticket Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    header('Location: ../pages/view_ticket.php?id=' . $incidentId . '&error=' . urlencode('Une erreur est survenue lors de la mise à jour du ticket'));
    exit;
}

