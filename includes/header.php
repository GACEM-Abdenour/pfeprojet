<?php
/**
 * Header / Topbar - shows current user and simple actions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role'] ?? '';
?>

<!-- DataTables CSS (CDN) for enhanced tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- Premium custom theme (after template CSS) -->
<link rel="stylesheet" href="../src/assets/css/custom.css">

<!-- Layout helpers: keep header fixed and avoid overlapping content -->
<style>
  /* Push page content below the fixed/sticky header */
  .page-wrapper[data-header-position="fixed"] .body-wrapper {
    padding-top: 4.5rem; /* approx. header height */
  }

  /* Make the app header behave like a modern sticky navbar */
  .app-header {
    position: sticky;
    top: 0;
    z-index: 1030; /* above sidebar and cards */
    background-color: #ffffff;
  }
</style>

<header class="app-header navbar navbar-expand px-4 py-2 border-bottom bg-white shadow-sm">
  <div class="container-fluid px-0 d-flex justify-content-between align-items-center">
    <div class="d-flex flex-column">
      <span class="fw-semibold text-dark">Plateforme GIA</span>
      <small class="text-muted">
        Gestion des Incidents Applicatifs
        <?php if ($role): ?>
          &nbsp;•&nbsp; Rôle :
          <strong><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></strong>
        <?php endif; ?>
      </small>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php if ($username): ?>
        <span class="text-muted small">
          Connecté en tant que
          <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong>
        </span>
      <?php endif; ?>
      <a href="../actions/logout_action.php" class="btn btn-sm btn-outline-danger">
        Déconnexion
      </a>
    </div>
  </div>
</header>

