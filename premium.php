<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'premium') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prémium oldal</title>
</head>
<body>
    <h2>Üdv, <?php echo htmlspecialchars($_SESSION['username']); ?> (Prémium)!</h2>
    <p>Itt láthatod a családtagok számára elérhető tartalmakat.</p>
    <a href="logout.php">Kijelentkezés</a>
</body>
</html>
