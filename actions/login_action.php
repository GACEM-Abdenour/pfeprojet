<?php
/**
 * Login Action - processes authentication form submission.
 * Redirects users to the appropriate dashboard based on role.
 */

session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php?error=' . urlencode('Méthode non autorisée'));
    exit;
}

require_once __DIR__ . '/../includes/db.php';

// Get and sanitize input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$remember = isset($_POST['remember']) ? true : false;

// Validate input
if ($username === '' || $password === '') {
    header('Location: ../pages/login.php?error=' . urlencode('Veuillez remplir tous les champs'));
    exit;
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, role, department
        FROM users
        WHERE username = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: ../pages/login.php?error=' . urlencode('Nom d\'utilisateur ou mot de passe incorrect'));
        exit;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['department'] = $user['department'];

    // Remember me cookie (optional)
    if ($remember) {
        $cookie_value = base64_encode($user['id'] . ':' . hash('sha256', $user['password_hash']));
        setcookie('gia_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }

    session_regenerate_id(true);

    // Redirect based on role
    switch ($user['role']) {
        case 'Technician':
            $redirect_url = '../pages/tech_dashboard.php';
            break;
        case 'Admin':
            $redirect_url = '../pages/admin_dashboard.php';
            break;
        case 'Reporter':
        default:
            $redirect_url = '../pages/reporter_dashboard.php';
            break;
    }

    header('Location: ' . $redirect_url);
    exit;

} catch (PDOException $e) {
    error_log('Authentication Error: ' . $e->getMessage());
    header('Location: ../pages/login.php?error=' . urlencode('Erreur de connexion. Veuillez réessayer plus tard.'));
    exit;
} catch (Exception $e) {
    error_log('Authentication Error: ' . $e->getMessage());
    header('Location: ../pages/login.php?error=' . urlencode('Une erreur est survenue. Veuillez réessayer plus tard.'));
    exit;
}

