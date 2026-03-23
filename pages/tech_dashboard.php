<?php
/**
 * Technician Dashboard - GIA Incident Management Platform
 * Shows assigned tickets and unassigned pool.
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

// Only technicians should see this page
requireRole('Technician');

$pdo = getDBConnection();
$currentUserId = getCurrentUserId();

// Fetch tickets assigned to this technician
$stmtMy = $pdo->prepare("
    SELECT i.id, i.title, i.category, i.priority, i.status, i.created_at
    FROM incidents i
    WHERE i.assigned_to = ?
    ORDER BY i.created_at DESC
");
$stmtMy->execute([$currentUserId]);
$myTickets = $stmtMy->fetchAll();

// Fetch unassigned tickets (Open / Diagnostic etc. but without assigned_to)
$stmtUnassigned = $pdo->query("
    SELECT i.id, i.title, i.category, i.priority, i.status, i.created_at
    FROM incidents i
    WHERE i.assigned_to IS NULL
    ORDER BY i.created_at DESC
");
$unassignedTickets = $stmtUnassigned->fetchAll();
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord - Technicien | GIA</title>
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
        <!-- My Tickets -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Mes tickets assignés</h5>
                <div class="table-responsive">
                  <table class="table table-hover table-borderless align-middle datatable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($myTickets)): ?>
                        <tr class="empty-state-row">
                          <td colspan="6" class="p-0 border-0">
                            <div class="empty-state">
                              <i class="ti ti-inbox empty-state-icon"></i>
                              <p class="empty-state-title">Super ! Votre file d'attente est vide.</p>
                              <p class="empty-state-text">Aucun ticket assigné pour le moment.</p>
                            </div>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($myTickets as $ticket): ?>
                          <tr>
                            <td>#<?php echo (int)$ticket['id']; ?></td>
                            <td>
                              <a href="view_ticket.php?id=<?php echo (int)$ticket['id']; ?>">
                                <?php echo escape($ticket['title']); ?>
                              </a>
                            </td>
                            <td><?php echo escape($ticket['category']); ?></td>
                            <td>
                              <span class="badge <?php echo getPriorityBadgeClass($ticket['priority']); ?>">
                                <?php echo escape($ticket['priority']); ?>
                              </span>
                            </td>
                            <td>
                              <span class="badge <?php echo getStatusBadgeClass($ticket['status']); ?>">
                                <?php echo escape($ticket['status']); ?>
                              </span>
                            </td>
                            <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Unassigned Tickets -->
        <div class="row">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Tickets non assignés</h5>
                <div class="table-responsive">
                  <table class="table table-hover table-borderless align-middle datatable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($unassignedTickets)): ?>
                        <tr class="empty-state-row">
                          <td colspan="6" class="p-0 border-0">
                            <div class="empty-state">
                              <i class="ti ti-inbox empty-state-icon"></i>
                              <p class="empty-state-title">Super ! Votre file d'attente est vide.</p>
                              <p class="empty-state-text">Aucun ticket en attente d'assignation.</p>
                            </div>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($unassignedTickets as $ticket): ?>
                          <tr>
                            <td>#<?php echo (int)$ticket['id']; ?></td>
                            <td>
                              <a href="view_ticket.php?id=<?php echo (int)$ticket['id']; ?>">
                                <?php echo escape($ticket['title']); ?>
                              </a>
                            </td>
                            <td><?php echo escape($ticket['category']); ?></td>
                            <td>
                              <span class="badge <?php echo getPriorityBadgeClass($ticket['priority']); ?>">
                                <?php echo escape($ticket['priority']); ?>
                              </span>
                            </td>
                            <td>
                              <span class="badge <?php echo getStatusBadgeClass($ticket['status']); ?>">
                                <?php echo escape($ticket['status']); ?>
                              </span>
                            </td>
                            <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- container-fluid -->
    </div> <!-- body-wrapper -->
  </div> <!-- page-wrapper -->

  <?php
  if (file_exists(__DIR__ . '/../includes/footer.php')) {
      include __DIR__ . '/../includes/footer.php';
  }
  ?>
</body>
</html>

