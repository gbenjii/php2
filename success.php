<?php
session_start();

// Ha nincs regisztráció, irányítsuk vissza a regisztrációs oldalra
if (!isset($_SESSION['registered']) || $_SESSION['registered'] !== true) {
    header('Location: register.php');
    exit;
}

// A session-ből eltávolíthatjuk a regisztráció jelölését, ha már megjelenítettük ezt az oldalt
unset($_SESSION['registered']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikeres regisztráció</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .success-container { width: 300px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; text-align: center; }
        .success-container h2 { color: #4CAF50; }
        .success-container a { display: block; margin-top: 20px; text-decoration: none; color: #4CAF50; border: 1px solid #4CAF50; padding: 10px; border-radius: 5px; transition: 0.3s; }
        .success-container a:hover { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <div class="success-container">
        <h2>Sikeres regisztráció!</h2>
        <p>Most már bejelentkezhetsz a fiókodba.</p>
        <a href="login.php">Vissza a bejelentkezéshez</a>
    </div>
</body>
</html>
