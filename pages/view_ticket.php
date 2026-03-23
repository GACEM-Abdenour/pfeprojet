<?php
/**
 * View Ticket - core incident detail & action page (premium layout).
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pdo        = getDBConnection();
$incidentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($incidentId <= 0) {
    header('Location: tech_dashboard.php?error=' . urlencode('Ticket introuvable'));
    exit;
}

// Fetch incident with reporter and technician info
$stmt = $pdo->prepare("
    SELECT 
        i.*,
        ru.username AS reporter_username,
        tu.username AS tech_username
    FROM incidents i
    LEFT JOIN users ru ON i.user_id = ru.id
    LEFT JOIN users tu ON i.assigned_to = tu.id
    WHERE i.id = ?
");
$stmt->execute([$incidentId]);
$incident = $stmt->fetch();

if (!$incident) {
    header('Location: tech_dashboard.php?error=' . urlencode('Ticket introuvable'));
    exit;
}

// Fetch logs (timeline)
$stmtLogs = $pdo->prepare("
    SELECT l.*, u.username
    FROM incident_logs l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.incident_id = ?
    ORDER BY l.timestamp ASC
");
$stmtLogs->execute([$incidentId]);
$logs = $stmtLogs->fetchAll();

// Technicians list for admin assignment dropdown
$technicians = [];
if (getCurrentUserRole() === 'Admin') {
    $techStmt = $pdo->query("
        SELECT id, username
        FROM users
        WHERE role = 'Technician'
        ORDER BY username
    ");
    $technicians = $techStmt->fetchAll();
}

$role           = getCurrentUserRole();
$currentUserId  = getCurrentUserId();
$isTechnician   = ($role === 'Technician');
$isAdmin        = ($role === 'Admin');
$assignedUserId = $incident['assigned_to'] ?? null;
$isUnassigned   = $assignedUserId === null;
$isAssignedToMe = (!$isUnassigned && (int)$assignedUserId === (int)$currentUserId);

$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') : '';
$error_message   = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : '';
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticket #<?php echo (int)$incident['id']; ?> - GIA</title>
  <link rel="shortcut icon" type="image/png" href="../src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../src/assets/css/styles.min.css" />
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
       data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

    <?php
    if (file_exists(__DIR__ . '/../includes/sidebar.php')) {
        include __DIR__ . '/../includes/sidebar.php';
    }
    if (file_exists(__DIR__ . '/../includes/header.php')) {
        include __DIR__ . '/../includes/header.php';
    }
    ?>

    <div class="body-wrapper">
      <div class="container-fluid py-4">
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-1 fw-semibold">
                Ticket #<?php echo (int)$incident['id']; ?> · <?php echo escape($incident['title']); ?>
              </h4>
              <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="badge rounded-pill px-3 <?php echo getPriorityBadgeClass($incident['priority']); ?>">
                  Priorité : <?php echo escape($incident['priority']); ?>
                </span>
                <span class="badge rounded-pill px-3 <?php echo getStatusBadgeClass($incident['status']); ?>">
                  Statut : <?php echo escape($incident['status']); ?>
                </span>
                <span class="badge rounded-pill px-3 bg-light text-dark border">
                  Catégorie : <?php echo escape($incident['category']); ?>
                </span>
              </div>
            </div>
            <a href="<?php
              if ($role === 'Technician') echo 'tech_dashboard.php';
              elseif ($role === 'Admin') echo 'admin_dashboard.php';
              else echo 'reporter_dashboard.php';
              ?>"
               class="btn btn-outline-secondary btn-sm">
              Retour aux tickets
            </a>
          </div>
        </div>

        <?php if ($success_message): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Succès :</strong> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erreur :</strong> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
          </div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Left column: details & history -->
          <div class="col-lg-8">
            <!-- Ticket details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="card-title mb-1">Détails de l'incident</h5>
                    <small class="text-muted">
                      Créé le <?php echo formatDateTime($incident['created_at']); ?>
                      <?php if (!empty($incident['updated_at'])): ?>
                        · Mis à jour le <?php echo formatDateTime($incident['updated_at']); ?>
                      <?php endif; ?>
                    </small>
                  </div>
                </div>

                <dl class="row small mb-0">
                  <dt class="col-sm-3 text-muted">Demandeur</dt>
                  <dd class="col-sm-9">
                    <?php echo escape($incident['reporter_username'] ?? ''); ?>
                  </dd>

                  <dt class="col-sm-3 text-muted">Technicien</dt>
                  <dd class="col-sm-9">
                    <?php if ($incident['tech_username']): ?>
                      <?php echo escape($incident['tech_username']); ?>
                    <?php else: ?>
                      <span class="text-muted">Non assigné</span>
                    <?php endif; ?>
                  </dd>
                </dl>

                <hr>

                <h6 class="fw-semibold mb-2">Description</h6>
                <p class="mb-0 lh-base">
                  <?php echo nl2br(escape($incident['description'])); ?>
                </p>
              </div>
            </div>

            <!-- Timeline / history -->
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Historique des actions</h5>

                <?php if (empty($logs)): ?>
                  <p class="text-muted mb-0">Aucune action enregistrée pour ce ticket pour le moment.</p>
                <?php else: ?>
                  <div class="timeline">
                    <?php foreach ($logs as $log): ?>
                      <div class="d-flex position-relative mb-4">
                        <div class="me-3">
                          <span class="rounded-circle bg-primary-subtle border border-primary d-inline-block"
                                style="width: 12px; height: 12px;"></span>
                          <div class="border-start ms-2 h-100 position-absolute top-0 translate-middle-y"
                               style="left: 6px; opacity: 0.4;"></div>
                        </div>
                        <div>
                          <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                            <span class="fw-semibold small">
                              <?php echo escape($log['action_type']); ?>
                            </span>
                            <span class="text-muted small">
                              <?php echo formatDateTime($log['timestamp']); ?>
                            </span>
                            <?php if (!empty($log['username'])): ?>
                              <span class="badge rounded-pill px-3 bg-light text-dark">
                                <?php echo escape($log['username']); ?>
                              </span>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($log['message'])): ?>
                            <div class="small text-body">
                              <?php echo nl2br(escape($log['message'])); ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Right column: actions -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Actions sur le ticket</h5>

                <!-- Admin: Assign to technician -->
                <?php if ($isAdmin): ?>
                  <form method="POST" action="../actions/update_ticket.php" class="mb-4">
                    <input type="hidden" name="incident_id" value="<?php echo (int)$incident['id']; ?>">
                    <input type="hidden" name="action" value="assign_tech">
                    <label for="assigned_to" class="form-label">Assigner à un technicien</label>
                    <select id="assigned_to" name="assigned_to" class="form-select mb-2" required>
                      <option value="">— Choisir un technicien —</option>
                      <option value="0" <?php echo $isUnassigned ? 'selected' : ''; ?>>— Non assigné —</option>
                      <?php foreach ($technicians as $tech): ?>
                        <option value="<?php echo (int)$tech['id']; ?>"
                          <?php echo ($assignedUserId !== null && (int)$assignedUserId === (int)$tech['id']) ? 'selected' : ''; ?>>
                          <?php echo escape($tech['username']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="mb-2">
                      <label for="assign_comment" class="form-label small text-muted">Commentaire (optionnel)</label>
                      <textarea id="assign_comment" name="assign_comment" rows="2" class="form-control form-control-sm"
                                placeholder="Note pour l'historique..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-sm">
                      Assigner ce ticket
                    </button>
                  </form>
                  <hr class="my-3">
                <?php endif; ?>

                <!-- Tech: Take ticket (unassigned only) -->
                <?php if ($isTechnician && $isUnassigned): ?>
                  <form method="POST" action="../actions/update_ticket.php" class="mb-4">
                    <input type="hidden" name="incident_id" value="<?php echo (int)$incident['id']; ?>">
                    <input type="hidden" name="action" value="take_ticket">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                      S'assigner ce ticket
                    </button>
                  </form>
                <?php endif; ?>

                <!-- Tech (assigned): full status + comment (required for Resolved / Failed/Blocked) -->
                <?php if ($isTechnician && !$isUnassigned && $isAssignedToMe): ?>
                  <form method="POST" action="../actions/update_ticket.php" id="tech-update-form">
                    <input type="hidden" name="incident_id" value="<?php echo (int)$incident['id']; ?>">
                    <input type="hidden" name="action" value="update_status">

                    <div class="mb-3">
                      <label for="status" class="form-label">Changer le statut</label>
                      <select id="status" name="status" class="form-select">
                        <option value="">(Ne pas changer)</option>
                        <?php
                        $statusOptions = [
                            'Open'           => 'Ouvert (remet en file non assignée)',
                            'Assigned'       => 'Assigné',
                            'Diagnostic'     => 'En diagnostic',
                            'Resolved'       => 'Résolu',
                            'Closed'         => 'Clôturé',
                            'Failed/Blocked' => 'Échec / Bloqué (abandon)',
                        ];
                        foreach ($statusOptions as $value => $label):
                        ?>
                          <option value="<?php echo $value; ?>"
                            <?php echo ($incident['status'] === $value) ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="form-text small">
                        Choisir « Ouvert » vous désassigne et remet le ticket dans la liste des non assignés.
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="comment" class="form-label">Commentaire ou rapport</label>
                      <textarea id="comment" name="comment" rows="4" class="form-control"
                                placeholder="Rapport de résolution, raison d'abandon, diagnostic..."
                                aria-describedby="comment-help"></textarea>
                      <div class="form-text small" id="comment-help">
                        <span id="comment-requirement">Obligatoire si vous choisissez « Résolu » ou « Échec / Bloqué ».</span>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                      Mettre à jour le ticket
                    </button>
                  </form>
                  <script>
                  (function() {
                    var statusSelect = document.getElementById('status');
                    var commentField = document.getElementById('comment');
                    if (!statusSelect || !commentField) return;
                    function toggleRequired() {
                      var v = (statusSelect.value || '').trim();
                      var required = (v === 'Resolved' || v === 'Failed/Blocked');
                      commentField.required = required;
                      commentField.setAttribute('aria-required', required ? 'true' : 'false');
                    }
                    statusSelect.addEventListener('change', toggleRequired);
                    toggleRequired();
                  })();
                  </script>
                <?php elseif ($isAdmin && !$isUnassigned): ?>
                  <!-- Admin: can also change status (same form as before but full options) -->
                  <form method="POST" action="../actions/update_ticket.php">
                    <input type="hidden" name="incident_id" value="<?php echo (int)$incident['id']; ?>">
                    <input type="hidden" name="action" value="update_status">

                    <div class="mb-3">
                      <label for="status_admin" class="form-label">Forcer le statut</label>
                      <select id="status_admin" name="status" class="form-select">
                        <option value="">(Ne pas changer)</option>
                        <?php
                        $statusOptions = [
                            'Open' => 'Ouvert',
                            'Assigned' => 'Assigné',
                            'Diagnostic' => 'En diagnostic',
                            'Resolved' => 'Résolu',
                            'Closed' => 'Clôturé',
                            'Failed/Blocked' => 'Échec / Bloqué',
                        ];
                        foreach ($statusOptions as $value => $label):
                        ?>
                          <option value="<?php echo $value; ?>"
                            <?php echo ($incident['status'] === $value) ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="comment_admin" class="form-label">Commentaire (optionnel)</label>
                      <textarea id="comment_admin" name="comment" rows="3" class="form-control"
                                placeholder="Note administrative..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary w-100">
                      Mettre à jour le statut
                    </button>
                  </form>
                <?php elseif ($isTechnician && !$isAssignedToMe && !$isUnassigned): ?>
                  <p class="text-muted small mb-0">
                    Ce ticket est déjà assigné à un autre technicien.
                  </p>
                <?php elseif (!$isAdmin && !$isTechnician): ?>
                  <p class="text-muted small mb-0">
                    Vous pouvez uniquement consulter ce ticket.
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php
  if (file_exists(__DIR__ . '/../includes/footer.php')) {
      include __DIR__ . '/../includes/footer.php';
  }
  ?>
</body>

</html>

