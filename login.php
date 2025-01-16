<?php
session_start();
$logFile = 'login_log.txt';
$error = "";

if (isset($_SESSION['username']) && isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
    // Már be van jelentkezve
    if ($_SESSION['role'] === 'owner') {
        header('Location: owner.php');
    } elseif ($_SESSION['role'] === 'admin') { // Admin ellenőrzése
        header('Location: admin.php');
    } elseif ($_SESSION['role'] === 'premium') {
        header('Location: premium.php');
    } else {
        header('Location: user.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $dataFile = 'users.txt';

    if (empty($username) || empty($password)) {
        $error = "A felhasználónév és a jelszó megadása kötelező!";
    } else {
        if (file_exists($dataFile)) {
            $users = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $loginSuccessful = false; // Logikai változó a sikeres bejelentkezéshez

            foreach ($users as $user) {
                list($savedUsername, $savedPassword, $savedRoles, $savedId) = explode('|', $user);
                if ($savedUsername === $username && password_verify($password, $savedPassword)) {
                    $_SESSION['username'] = $username;
                    $roles = explode(',', $savedRoles);
                    $priority = ['owner', 'admin', 'premium', 'felhasználó'];
                    $highestRole = null;

                    foreach ($priority as $role) {
                        if (in_array($role, $roles)) {
                            $highestRole = $role;
                            break;
                        }
                    }

                    $_SESSION['role'] = $highestRole;
                    $_SESSION['user_id'] = $savedId;
                    $loginSuccessful = true; // Sikeres bejelentkezés

                    // Sikeres bejelentkezés logolása
                    $logMessage = date('Y-m-d H:i:s') . ' - Sikeres bejelentkezés:' . PHP_EOL;
                    $logMessage .= 'Felhasználónév: ' . $username . PHP_EOL;
                    $logMessage .= 'ID: ' . $savedId . PHP_EOL;
                    $logMessage .= 'Böngésző: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
                    $logMessage .= 'IP cím: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
                    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);

                    // Átirányítás rang alapján (a logolás után)
                    if ($highestRole === 'owner') {
                        header('Location: owner.php');
                    } elseif ($highestRole === 'admin') {
                        header('Location: admin.php');
                    } elseif ($highestRole === 'premium') {
                        header('Location: premium.php');
                    } else {
                        header('Location: user.php');
                    }
                    exit;
                }
            }
            if (!$loginSuccessful) { // Ha nem volt sikeres bejelentkezés
                // Sikertelen bejelentkezési kísérlet logolása
                $logMessage = date('Y-m-d H:i:s') . ' - Sikertelen bejelentkezési kísérlet:' . PHP_EOL;
                $logMessage .= 'Felhasználónév: ' . $username . PHP_EOL;
                $logMessage .= 'IP cím: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
                file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
                $error = "Hibás felhasználónév vagy jelszó!";
            }
        } else {
            $error = "A felhasználói adatokat tartalmazó fájl nem található!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .login-form {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-bottom: 10px;
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="login-form">
        <h2>Bejelentkezés</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Felhasználónév" required><br>
            <input type="password" name="password" placeholder="Jelszó" required><br>
            <button type="submit">Bejelentkezés</button>
        </form>

        <div class="register-link">
            <a href="register.php">Regisztrálok</a>
        </div>
    </div>

</body>

</html>