<?php
require 'auth.php';
requireRole('felhasználó'); // Minimum "felhasználó" rang kell

?>
<!DOCTYPE html>
<html>
<head>
    <title>Felhasználói oldal</title>
</head>
<body>
    <h2>Üdv, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Ez a felhasználók számára elérhető tartalom.</p>
    <a href="logout.php">Kijelentkezés</a>
</body>
<a href="globalchat.php">Tovább a globalchatre</a>
</html>
