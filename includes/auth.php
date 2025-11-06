<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}
