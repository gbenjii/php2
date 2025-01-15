<?php
session_start();

// Ellenőrizzük, hogy be vagyunk-e jelentkezve
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "Be kell jelentkezned, hogy használhasd a chatet!";
    exit;
}

// Üzenetek betöltése a messages.txt fájlból
$messages = file_exists('messages.txt') ? file_get_contents('messages.txt') : '';

if (!empty($messages)) {
    echo nl2br(htmlspecialchars($messages)); // Az üzenetek megjelenítése
} else {
    echo "Nincs üzenet még a chatben.";
}

?>

