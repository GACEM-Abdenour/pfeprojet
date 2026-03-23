<?php
/**
 * Create Ticket Handler - GIA Incident Management Platform
 * Processes the ticket creation form for Reporter users.
 */

session_start();
require_once __DIR__ . '/functions.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/create_ticket.php?error=' . urlencode('Méthode non autorisée'));
    exit;
}

// Only Reporters can create tickets
requireRole('Reporter');

$title       = isset($_POST['title']) ? trim($_POST['title']) : '';
$category    = isset($_POST['category']) ? trim($_POST['category']) : '';
$priority    = isset($_POST['priority']) ? trim($_POST['priority']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Basic validation
if ($title === '' || $category === '' || $priority === '' || $description === '') {
    header('Location: ../pages/create_ticket.php?error=' . urlencode('Veuillez remplir tous les champs obligatoires'));
    exit;
}

// Validate category and priority against allowed values
$allowedCategories = ['Hardware', 'Software', 'Network', 'Access'];
$allowedPriorities = ['Critical', 'Major', 'Minor'];

if (!in_array($category, $allowedCategories, true) || !in_array($priority, $allowedPriorities, true)) {
    header('Location: ../pages/create_ticket.php?error=' . urlencode('Valeurs de catégorie ou de priorité invalides'));
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    $userId = getCurrentUserId();
    if (!$userId) {
        throw new Exception('Utilisateur non authentifié');
    }

    // Insert incident
    $stmt = $pdo->prepare("
        INSERT INTO incidents (user_id, assigned_to, title, description, category, priority, status, created_at)
        VALUES (?, NULL, ?, ?, ?, ?, 'Open', GETDATE())
    ");

    $stmt->execute([
        $userId,
        $title,
        $description,
        $category,
        $priority,
    ]);

    $incidentId = (int)$pdo->lastInsertId();

    // Handle optional attachment
    if (
        isset($_FILES['attachment']) &&
        is_array($_FILES['attachment']) &&
        $_FILES['attachment']['error'] === UPLOAD_ERR_OK &&
        $_FILES['attachment']['size'] > 0
    ) {
        $uploadDir = realpath(__DIR__ . '/../uploads');
        if ($uploadDir === false) {
            // Try to create directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                throw new Exception('Impossible de créer le dossier des pièces jointes');
            }
        }

        $originalName = $_FILES['attachment']['name'];
        $tmpPath      = $_FILES['attachment']['tmp_name'];
        $size         = (int)$_FILES['attachment']['size'];

        // Basic size limit: 5 MB
        $maxSize = 5 * 1024 * 1024;
        if ($size > $maxSize) {
            throw new Exception('La pièce jointe dépasse la taille maximale autorisée (5 Mo)');
        }

        // Sanitize filename
        $safeName   = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($originalName));
        $uniqueName = 'incident_' . $incidentId . '_' . time() . '_' . $safeName;

        $destinationPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;

        if (!move_uploaded_file($tmpPath, $destinationPath)) {
            throw new Exception("Échec du téléchargement de la pièce jointe");
        }

        // Store relative path for web usage
        $relativePath = 'uploads/' . $uniqueName;

        $stmtAtt = $pdo->prepare("
            INSERT INTO attachments (incident_id, file_path, file_name, uploaded_at)
            VALUES (?, ?, ?, GETDATE())
        ");

        $stmtAtt->execute([
            $incidentId,
            $relativePath,
            $originalName,
        ]);
    }

    // Log creation action
    logIncidentAction($incidentId, $userId, 'Creation', 'Ticket créé par le déclarant');

    $pdo->commit();

    header('Location: ../pages/create_ticket.php?success=' . urlencode('Ticket créé avec succès'));
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Create Ticket Error: ' . $e->getMessage());
    header('Location: ../pages/create_ticket.php?error=' . urlencode('Une erreur est survenue lors de la création du ticket'));
    exit;
}

