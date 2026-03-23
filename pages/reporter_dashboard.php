<?php
/**
 * Reporter Dashboard - lists tickets created by the current reporter.
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

requireRole('Reporter');

$pdo        = getDBConnection();
$currentId  = getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT i.id, i.title, i.category, i.priority, i.status, i.created_at
    FROM incidents i
    WHERE i.user_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$currentId]);
$tickets = $stmt->fetchAll();

$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') : '';
$error_message   = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : '';
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mes tickets - GIA</title>
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
            <h3 class="mb-0">Mes tickets</h3>
            <a href="create_ticket.php" class="btn btn-primary">Nouveau ticket</a>
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

        <div class="row">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Historique de mes incidents</h5>
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
                      <?php if (empty($tickets)): ?>
                        <tr class="empty-state-row">
                          <td colspan="6" class="p-0 border-0">
                            <div class="empty-state">
                              <i class="ti ti-inbox empty-state-icon"></i>
                              <p class="empty-state-title">Super ! Votre file d'attente est vide.</p>
                              <p class="empty-state-text">Vous n'avez pas encore créé de ticket.</p>
                            </div>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
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

