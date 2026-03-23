<?php
/**
 * Authentication Handler - GIA Incident Management Platform
 * Handles user login and session management
 * 
 * Security: Uses prepared statements, password_verify(), and proper session handling
 */

session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php?error=' . urlencode('Méthode non autorisée'));
    exit;
}

// Include database connection
require_once __DIR__ . '/db.php';

// Get and sanitize input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$remember = isset($_POST['remember']) ? true : false;

// Validate input
if (empty($username) || empty($password)) {
    header('Location: ../pages/login.php?error=' . urlencode('Veuillez remplir tous les champs'));
    exit;
}

try {
    // Get database connection
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Query user by username (using prepared statement)
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, department FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Log failed login attempt (optional - could add to incident_logs or separate audit table)
        header('Location: ../pages/login.php?error=' . urlencode('Nom d\'utilisateur ou mot de passe incorrect'));
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['department'] = $user['department'];
    
    // Set remember me cookie (optional - 30 days)
    if ($remember) {
        $cookie_value = base64_encode($user['id'] . ':' . hash('sha256', $user['password_hash']));
        setcookie('gia_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Redirect based on role
    $redirect_url = '';
    switch ($user['role']) {
        case 'Technician':
            $redirect_url = '../pages/tech_dashboard.php';
            break;
        case 'Admin':
            $redirect_url = '../pages/admin_dashboard.php';
            break;
        case 'Reporter':
        default:
            $redirect_url = '../pages/create_ticket.php';
            break;
    }
    
    header('Location: ' . $redirect_url);
    exit;
    
} catch (PDOException $e) {
    // Log error securely
    error_log("Authentication Error: " . $e->getMessage());
    header('Location: ../pages/login.php?error=' . urlencode('Erreur de connexion. Veuillez réessayer plus tard.'));
    exit;
} catch (Exception $e) {
    // Log error securely
    error_log("Authentication Error: " . $e->getMessage());
    header('Location: ../pages/login.php?error=' . urlencode('Une erreur est survenue. Veuillez réessayer plus tard.'));
    exit;
}
