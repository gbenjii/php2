<?php
session_start();

if (isset($_SESSION['registered']) && $_SESSION['registered'] === true) {
    header('Location: success.php');
    exit;
}

$profile_pics_dir = 'profile_pics';
$default_profile_pics_dir = 'default_profile_pics';
$logFile = 'registersucces_log.txt'; // Logfájl neve

if (!is_dir($profile_pics_dir)) {
    mkdir($profile_pics_dir, 0755, true);
}

if (!is_dir($default_profile_pics_dir)) {
    mkdir($default_profile_pics_dir, 0755, true);
    $default_image_path = $default_profile_pics_dir . '/default.png';
    if (!file_exists($default_image_path)) {
        $default_image = imagecreate(200, 200);
        $bg = imagecolorallocate($default_image, 200, 200, 200);
        imagepng($default_image, $default_image_path);
        imagedestroy($default_image);
    }
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errorMessage = "A felhasználónév és a jelszó megadása kötelező!";
    } else {
        $users = file('users.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newId = 1;

        foreach ($users as $user) {
            $userData = explode('|', $user);
            if (count($userData) < 4) {
                continue;
            }
            list($storedUsername, $storedPassword, $storedRole, $storedId) = $userData;

            if ($storedUsername === $username) {
                $errorMessage = "A felhasználónév már létezik!";
                break;
            }
            $newId = max($newId, (int)$storedId + 1);
        }

        if (!isset($errorMessage)) {
            $role = 'felhasználó';
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $newUser = $username . '|' . $hashedPassword . '|' . $role . '|' . $newId . PHP_EOL;
            file_put_contents('users.txt', $newUser, FILE_APPEND);

            $default_profile_pic = $default_profile_pics_dir . '/default.png';
            $profile_pic_path = $profile_pics_dir . '/' . $newId . '.png';
            if (!copy($default_profile_pic, $profile_pic_path)) {
                error_log("Hiba a profilkép másolásakor!");
                $errorMessage = "Hiba történt a regisztráció során!";
            }

            if (!isset($errorMessage)) {
                $_SESSION['registered'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['user_id'] = $newId;

                // Logolás
                $logMessage = date('Y-m-d H:i:s') . ' - Sikeres regisztráció:' . PHP_EOL;
                $logMessage .= 'Felhasználónév: ' . $username . PHP_EOL;
                $logMessage .= 'Jelszó (hash): ' . $hashedPassword . PHP_EOL; // A hashelt jelszót logoljuk!
                $logMessage .= 'ID: ' . $newId . PHP_EOL;
                $logMessage .= 'Böngésző: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
                $logMessage .= 'IP cím: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
                file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);

                header('Location: success.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .register-form { width: 300px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;}
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .error { color: red; font-size: 14px; text-align: center; margin-bottom: 10px;}
        .login-link { text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="register-form">
        <h2>Regisztráció</h2>
        <?php if (isset($errorMessage)): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Felhasználónév" required><br>
            <input type="password" name="password" placeholder="Jelszó" required><br>
            <button type="submit">Regisztrálok</button>
        </form>
        <div class="login-link">
            <a href="login.php">Már van fiókom</a>
        </div>
    </div>
</body>
</html>