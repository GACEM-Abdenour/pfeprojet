<?php
/**
 * Helper Functions - GIA Incident Management Platform
 * Common utility functions used across the application
 */

require_once __DIR__ . '/db.php';

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in, redirect to login if not
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../pages/login.php?error=' . urlencode('Veuillez vous connecter pour accéder à cette page'));
        exit;
    }
}

/**
 * Require specific role, redirect if user doesn't have required role
 * 
 * @param string|array $required_roles Role(s) required
 * @return void
 */
function requireRole($required_roles) {
    requireLogin();
    
    if (!isset($_SESSION['role'])) {
        header('Location: ../pages/login.php?error=' . urlencode('Session invalide'));
        exit;
    }
    
    $user_role = $_SESSION['role'];
    
    if (is_array($required_roles)) {
        if (!in_array($user_role, $required_roles)) {
            header('Location: ../pages/login.php?error=' . urlencode('Accès non autorisé'));
            exit;
        }
    } else {
        if ($user_role !== $required_roles) {
            header('Location: ../pages/login.php?error=' . urlencode('Accès non autorisé'));
            exit;
        }
    }
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Logout user and destroy session
 * 
 * @return void
 */
function logout() {
    session_start();
    $_SESSION = array();
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy remember me cookie
    if (isset($_COOKIE['gia_remember'])) {
        setcookie('gia_remember', '', time() - 3600, '/');
    }
    
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

/**
 * Log an incident action to incident_logs table
 * 
 * @param int $incident_id Incident ID
 * @param int $user_id User ID performing the action
 * @param string $action_type Type of action (e.g., 'Status Change', 'Comment', 'Assignment')
 * @param string|null $message Optional message describing the action
 * @return bool True on success, false on failure
 */
function logIncidentAction($incident_id, $user_id, $action_type, $message = null) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO incident_logs (incident_id, user_id, action_type, message, timestamp) 
            VALUES (?, ?, ?, ?, GETDATE())
        ");
        
        return $stmt->execute([$incident_id, $user_id, $action_type, $message]);
        
    } catch (PDOException $e) {
        error_log("Error logging incident action: " . $e->getMessage());
        return false;
    }
}

/**
 * Sanitize output for HTML display
 * 
 * @param string $string String to sanitize
 * @return string Sanitized string
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format datetime for display
 * 
 * @param string $datetime DateTime string from database
 * @param string $format Format string (default: French format)
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) {
        return '';
    }
    
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return $datetime;
    }
    
    return date($format, $timestamp);
}

/**
 * Get status badge HTML class for Bootstrap
 * 
 * @param string $status Status value
 * @return string Bootstrap badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Open' => 'bg-secondary',
        'Assigned' => 'bg-info',
        'Diagnostic' => 'bg-warning',
        'Resolved' => 'bg-success',
        'Closed' => 'bg-dark',
        'Failed/Blocked' => 'bg-danger'
    ];
    
    $bg = isset($classes[$status]) ? $classes[$status] : 'bg-secondary';
    // Always return pill styling + background class
    return 'rounded-pill px-3 ' . $bg;
}

/**
 * Get priority badge HTML class for Bootstrap
 * 
 * @param string $priority Priority value
 * @return string Bootstrap badge class
 */
function getPriorityBadgeClass($priority) {
    $classes = [
        'Critical' => 'bg-danger',
        'Major' => 'bg-warning',
        'Minor' => 'bg-info'
    ];
    
    return isset($classes[$priority]) ? $classes[$priority] : 'bg-secondary';
}
