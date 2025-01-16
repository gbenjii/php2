<?php
session_start();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// Ha nincs bejelentkezve a felhasználó, átirányítás a login.php-ra
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dataFile = 'users.txt';
$userId = (int) $_SESSION['user_id'];
$currentUser = null;
$errorMessage = null;
$successMessage = null;
$profileImageDir = 'profile_pics/';
$profileImagePath = null;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

if (file_exists($dataFile)) {
    $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        $userData = explode('|', $user);
        if (count($userData) != 4) { // Fontos ellenőrzés!
            error_log("Hibás sor a users.txt fájlban: " . $user);
            continue;
        }
        list($savedUsername, $savedPassword, $savedRoles, $savedId) = $userData;
        if ((int) $savedId === $userId) {
            $currentUser = [
                'username' => $savedUsername,
                'roles' => $savedRoles,
                'id' => $savedId
            ];
            break;
        }
    }
}

if (!$currentUser) {
    $errorMessage = "Hiba: A felhasználó nem található!";
}

if ($currentUser) {
    foreach ($allowedExtensions as $ext) {
        $path = $profileImageDir . $userId . '.' . $ext;
        if (file_exists($path)) {
            $profileImagePath = $path;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && $currentUser) {
    if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($profileImageDir)) {
            mkdir($profileImageDir, 0755, true);
        }

        $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, $allowedExtensions)) {
            foreach ($allowedExtensions as $ext) {
                $oldPath = $profileImageDir . $userId . '.' . $ext;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $imagePath = $profileImageDir . $userId . '.' . $extension;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $imagePath)) {
                $successMessage = "Profilkép sikeresen frissítve!";
                header("Location: profile.php");
                exit;
            } else {
                $errorMessage = "Hiba a fájl mentésekor!";
            }
        } else {
            $errorMessage = "Nem engedélyezett fájltípus!";
        }
    } elseif ($_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errorMessage = "Hiba történt a fájl feltöltése során. Kód: " . $_FILES['profile_image']['error'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && $currentUser) {
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);
    $usernameExists = false;

    $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        list($storedUsername, $storedPassword, $storedRoles, $storedId) = explode('|', $user);
        if ($storedUsername === $newUsername && (int) $storedId !== $userId) {
            $usernameExists = true;
            $errorMessage = "Ez a felhasználónév már foglalt!";
            break;
        }
    }

    if (!$usernameExists) {
        foreach ($users as $index => $user) {
            list($savedUsername, $savedPassword, $storedRoles, $storedId) = explode('|', $user);
            if ((int) $storedId === $userId) {
                $hashedPassword = !empty($newPassword) ? password_hash($newPassword, PASSWORD_DEFAULT) : $savedPassword;
                $users[$index] = $newUsername . '|' . $hashedPassword . '|' . $storedRoles . '|' . $storedId;
                $_SESSION['username'] = $newUsername;
                file_put_contents($dataFile, implode("\n", $users) . "\n");
                $successMessage = "Profil sikeresen frissítve!";
                header("Location: profile.php");
                exit;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Profil</title>
</head>
<style>
    body {
    font-family: sans-serif;
    margin: 20px;
    background-color: #f4f4f4; /* Halvány szürke háttér */
    color: #333; /* Sötétszürke szöveg */
}

.container {
    width: 500px; /* Szélesebb konténer */
    margin: 0 auto;
    background-color: #fff; /* Fehér háttér a konténernek */
    border: 1px solid #ddd; /* Világosszürke keret */
    padding: 20px;
    border-radius: 8px; /* Lekerekített sarkok */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Enyhe árnyék */
}

h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

.error {
    color: #a94442; /* Sötétvörös hibaüzenet */
    background-color: #f2dede; /* Világosvörös háttér a hibaüzenetnek */
    border: 1px solid #ebccd1; /* Vörös keret */
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.success {
    color: #3c763d; /* Sötétzöld sikerüzenet */
    background-color: #dff0d8; /* Világoszöld háttér a sikerüzenetnek */
    border: 1px solid #d6e9c6; /* Zöld keret */
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold; /* Félkövér címkék */
    color: #555;
}

input[type="text"],
input[type="password"],
input[type="file"] {
    width: calc(100% - 12px); /* Hely a paddingnek */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    margin-bottom: 15px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50; /* Zöld gomb */
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease; /* Sima áttűnés */
}

button:hover {
    background-color: #45a049; /* Sötétebb zöld hover */
}

.profile-image {
    text-align: center;
    margin-bottom: 20px;
}

.profile-image img {
    max-width: 150px;
    border-radius: 50%;
    border: 3px solid #ddd; /* Vastagabb, világosszürke keret a képnek */
    display: block;
    margin: 0 auto;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Enyhe árnyék a képnek */
}

/* Reszponzív design kisebb képernyőkre */
@media (max-width: 600px) {
    .container {
        width: 90%;
    }
}
</style>

<body>
    <div class="container">
        <h2>Profil</h2>

        <?php if ($errorMessage): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <p class="success"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <?php if ($currentUser): ?>
            <div class="profile-image">
                <h3>Profilkép</h3>
                <?php if ($profileImagePath): ?>
                    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Profilkép">
                <?php else: ?>
                    <img src="default-profile.jpg" alt="Nincs profilkép">
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="profile_image" accept="image/*">
                    <button type="submit">Profilkép feltöltése</button>
                </form>
            </div>

            <form method="post">
                <h3>Adatok</h3>
                <label for="username">Felhasználónév:</label>
                <input type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>

                <label for="password">Új jelszó (opcionális):</label>
                <input type="password" id="password" name="password" placeholder="Új jelszó">

                <h3>Rang:</h3>
                <p><?php echo htmlspecialchars($currentUser['roles']); ?></p>

                <h3>Felhasználó ID:</h3>
                <p><?php echo htmlspecialchars($currentUser['id']); ?></p>

                <button type="submit" name="update_profile">Profil frissítése</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>