<?php
session_start();

// Rangok prioritása
$rolePriority = [
    'admin' => 3,
    'premium' => 2,
    'felhasználó' => 1
];

// Az oldalhoz szükséges minimum rang
function requireRole($requiredRole) {
    global $rolePriority;

    // Ha nincs bejelentkezve
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }

    // Ha a felhasználó rangja nem elég magas
    if ($rolePriority[$_SESSION['role']] < $rolePriority[$requiredRole]) {
        echo "Nincs jogosultságod az oldal megtekintéséhez!";
        exit;
    }
}
?>
