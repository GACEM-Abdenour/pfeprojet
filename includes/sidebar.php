<?php
/**
 * Sidebar - dynamic navigation based on user role.
 * Expected to be included inside the NiceAdmin layout.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? null;
?>

<aside class="left-sidebar">
  <div class="brand-logo d-flex align-items-center justify-content-between px-4 py-3">
    <a href="../index.php" class="text-nowrap logo-img">
      <h2 class="text-primary fw-bold mb-0">GIA</h2>
    </a>
  </div>
  <nav class="sidebar-nav px-4">
    <ul id="sidebarnav">
      <?php if ($role === 'Reporter'): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="../pages/reporter_dashboard.php">
            <span class="hide-menu">Mes tickets</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="../pages/create_ticket.php">
            <span class="hide-menu">Nouveau ticket</span>
          </a>
        </li>
      <?php elseif ($role === 'Technician'): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="../pages/tech_dashboard.php">
            <span class="hide-menu">Tableau de bord technicien</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="../pages/tech_dashboard.php">
            <span class="hide-menu">Tous les tickets</span>
          </a>
        </li>
      <?php elseif ($role === 'Admin'): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="../pages/admin_dashboard.php">
            <span class="hide-menu">Tableau de bord admin</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <span class="hide-menu">Gérer les utilisateurs</span>
          </a>
        </li>
      <?php endif; ?>

      <li class="sidebar-item mt-3">
        <a class="sidebar-link text-danger" href="../actions/logout_action.php">
          <span class="hide-menu">Déconnexion</span>
        </a>
      </li>
    </ul>
  </nav>
</aside>

