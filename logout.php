<?php
session_start();

// Log the logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    
    $pdo = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action, table_affected, record_id, ip_address) 
        VALUES (?, 'LOGOUT', 'users', ?, ?)
    ");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $ip]);
}

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: index.php?message=logged_out');
exit;
?>