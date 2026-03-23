<?php
/**
 * Reporter Dashboard - Create Ticket
 * Uses NiceAdmin layout with header + sidebar includes
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';

// Only allow authenticated Reporters to access this page
requireRole('Reporter');

$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8');
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Créer un ticket - GIA Incident Management</title>
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

      <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Créer un nouveau ticket</h3>
          </div>
        </div>

        <!-- Alerts -->
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

        <!-- Create Ticket Card -->
        <div class="row">
          <div class="col-lg-8 col-md-10">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title mb-4">Formulaire de création d'incident</h5>

                <form method="POST"
                      action="../actions/submit_ticket.php"
                      enctype="multipart/form-data">

                  <!-- Title -->
                  <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input
                      type="text"
                      class="form-control"
                      id="title"
                      name="title"
                      maxlength="255"
                      required
                    >
                  </div>

                  <!-- Category -->
                  <div class="mb-3">
                    <label for="category" class="form-label">Catégorie</label>
                    <select
                      class="form-select"
                      id="category"
                      name="category"
                      required
                    >
                      <option value="" selected disabled>Choisir une catégorie</option>
                      <option value="Hardware">Matériel (Hardware)</option>
                      <option value="Software">Logiciel (Software)</option>
                      <option value="Network">Réseau (Network)</option>
                      <option value="Access">Accès (Access)</option>
                    </select>
                  </div>

                  <!-- Priority -->
                  <div class="mb-3">
                    <label for="priority" class="form-label">Priorité</label>
                    <select
                      class="form-select"
                      id="priority"
                      name="priority"
                      required
                    >
                      <option value="" selected disabled>Choisir une priorité</option>
                      <option value="Critical">Critique</option>
                      <option value="Major">Majeure</option>
                      <option value="Minor">Mineure</option>
                    </select>
                  </div>

                  <!-- Description -->
                  <div class="mb-3">
                    <label for="description" class="form-label">Description détaillée</label>
                    <textarea
                      class="form-control"
                      id="description"
                      name="description"
                      rows="6"
                      required
                    ></textarea>
                  </div>

                  <!-- File Attachment -->
                  <div class="mb-4">
                    <label for="attachment" class="form-label">
                      Pièce jointe (capture d'écran, journal d'erreur, etc.)
                    </label>
                    <input
                      class="form-control"
                      type="file"
                      id="attachment"
                      name="attachment"
                      accept=".png,.jpg,.jpeg,.pdf,.txt"
                    >
                    <div class="form-text">
                      Formats acceptés : PNG, JPG, PDF, TXT. Taille max recommandée : 5 Mo.
                    </div>
                  </div>

                  <!-- Submit -->
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                      Enregistrer le ticket
                    </button>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- container-fluid -->
    </div> <!-- body-wrapper -->
  </div> <!-- page-wrapper -->

  <script src="../src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>
</html>

