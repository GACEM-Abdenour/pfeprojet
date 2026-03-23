<?php
/**
 * Index Page - Router/Redirect
 * Redirects to login page or appropriate dashboard based on session
 */

session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // User is logged in, redirect to appropriate dashboard
    $role = $_SESSION['role'];
    
    if ($role == 'Technician') {
        header('Location: pages/tech_dashboard.php');
    } elseif ($role == 'Admin') {
        header('Location: pages/admin_dashboard.php');
    } else {
        header('Location: pages/reporter_dashboard.php');
    }
    exit;
} else {
    // Not logged in, redirect to login
    header('Location: pages/login.php');
    exit;
}
