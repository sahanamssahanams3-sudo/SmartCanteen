<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirectByRole(): void {
    if (!isset($_SESSION['user']['role'])) {
        header('Location: ' . (dirname($_SERVER['SCRIPT_NAME']) === '/smartcanteen' ? '/smartcanteen/index.php' : '/smartcanteen/index.php'));
        exit;
    }

    switch ($_SESSION['user']['role']) {
        case 'admin': header('Location: /smartcanteen/admin/dashboard.php'); break;
        case 'staff': header('Location: /smartcanteen/staff/dashboard.php'); break;
        default: header('Location: /smartcanteen/user/dashboard.php'); break;
    }
    exit;
}

function requireRole(string $role): void {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . (dirname($_SERVER['SCRIPT_NAME']) === '/smartcanteen' ? '/smartcanteen/index.php' : '/smartcanteen/index.php'));
        exit;
    }
    if ($_SESSION['user']['role'] !== $role) {
        redirectByRole();
    }
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
