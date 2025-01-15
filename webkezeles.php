<?php
session_start();

// Csak adminok érhetik el az oldalt
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Az admin által szerkeszthető mappa
$editableDirectory = __DIR__; // Az aktuális mappa (ahol az admin_editor.php van)

// A fájl megtekintése vagy módosítása
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filePath = realpath($editableDirectory . '/' . $_POST['file']);
    
    // Biztonsági ellenőrzés: A fájl az engedélyezett mappában legyen
    if (strpos($filePath, $editableDirectory) !== 0 || !is_file($filePath)) {
        echo "Érvénytelen fájl!";
        exit;
    }
    
    // Ha mentés történt
    if (isset($_POST['save']) && isset($_POST['content'])) {
        file_put_contents($filePath, $_POST['content']);
        echo "<p>A fájl sikeresen mentve!</p>";
    }

    // A fájl tartalmának betöltése
    $fileContent = file_get_contents($filePath);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Fájl szerkesztése: <?php echo htmlspecialchars($_POST['file']); ?></title>
    </head>
    <body>
        <h2>Fájl szerkesztése: <?php echo htmlspecialchars($_POST['file']); ?></h2>
        <form method="post">
            <textarea name="content" rows="25" cols="100"><?php echo htmlspecialchars($fileContent); ?></textarea><br><br>
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($_POST['file']); ?>">
            <button type="submit" name="save">Mentés</button>
        </form>
        <br>
        <a href="admin.php">Vissza a fájllistához</a>
    </body>
    </html>
    <?php
    exit;
}

// A mappa összes fájljának listázása
$files = scandir($editableDirectory);

// Töröljük a . és .. elemeket (a jelenlegi és a szülő könyvtár)
$files = array_diff($files, array('.', '..'))
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin oldalszerkesztő</title>
</head>
<body>
    <h2>Üdv, <?php echo htmlspecialchars($_SESSION['username']); ?>! (Admin)</h2>
    <p>Itt szerkesztheted az oldal PHP fájljait.</p>
    <h3>Elérhető fájlok:</h3>
    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <?php echo htmlspecialchars($file); ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                    <button type="submit">Szerkesztés</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <br>
    <a href="logout.php">Kijelentkezés</a>
</body>
</html>


