<?php
session_start();

// Csak adminok érhetik el az oldalt
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
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
}
?>
<!DOCTYPE HTML>
<html lang="hu">

<head>
    <title>Benji</title>
    <meta charset="utf-8" />
    <meta name="keyworlds" content="benji, benjiweb, benjiwebsite, benjiweboldal, benjihome">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/admin.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/Home/">Home</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="webkezeles.php">WebKezelés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="felhasznalok.php">Felhasználók</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Globalchat.php">Globalchat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="files.php">Fájlok</a>
                    </li>
                    <li class="nav-item"></li>
                    <a class="nav-link" href="logout.php"><strong>Kijelentkezés</strong></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <strong>
        <h2>Üdv, <?php echo htmlspecialchars($_SESSION['username']); ?>!(owner)</h2>
    </strong>

</body>


<!--amit használtam kódot-->
<!--https://getbootstrap.com/-->

</html>