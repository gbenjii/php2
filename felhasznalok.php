<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$usersFile = 'users.txt';
$deletedUsersFile = 'deleted_users.txt';

// Felhasználók betöltése
$users = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$deletedUsers = file($deletedUsersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Ha törlésre kerültek felhasználók
if (isset($_POST['delete_users'])) {
    $selectedUsers = $_POST['selected_users'] ?? [];

    foreach ($selectedUsers as $username) {
        foreach ($users as $index => $user) {
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            if ($savedUsername === $username) {
                // Felhasználó eltávolítása a users.txt-ből
                unset($users[$index]);

                // Törölt felhasználó hozzáadása a deleted_users.txt-hez
                file_put_contents($deletedUsersFile, "$user\n", FILE_APPEND);
            }
        }
    }

    // Frissített users.txt fájl mentése
    file_put_contents($usersFile, implode("\n", $users) . "\n");
}

// Ha visszaállítjuk a felhasználót a törölt listából
if (isset($_POST['restore_user'])) {
    $usernameToRestore = $_POST['restore_user'];

    foreach ($deletedUsers as $index => $deletedUser) {
        list($deletedUsername, $deletedPassword, $deletedRoles) = explode('|', $deletedUser);
        if ($deletedUsername === $usernameToRestore) {
            // Felhasználó visszaállítása a users.txt-be
            file_put_contents($usersFile, "$deletedUser\n", FILE_APPEND);

            // Törölt felhasználó eltávolítása a deleted_users.txt-ből
            unset($deletedUsers[$index]);
        }
    }

    // Frissített deleted_users.txt fájl mentése
    file_put_contents($deletedUsersFile, implode("\n", $deletedUsers) . "\n");
}

// Rang módosítása
if (isset($_POST['update_role'])) {
    $usernameToUpdate = $_POST['username_to_update'];
    $newRoles = $_POST['new_roles'] ?? []; // Ha nem választanak ki semmit, üres lesz

    foreach ($users as $index => $user) {
        list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
        if ($savedUsername === $usernameToUpdate) {
            // Frissített felhasználó adatok
            $updatedUser = $savedUsername . '|' . $savedPassword . '|' . implode(',', $newRoles);
            $users[$index] = $updatedUser;
        }
    }

    // Frissített users.txt fájl mentése
    file_put_contents($usersFile, implode("\n", $users) . "\n");
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználók kezelése</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .user-management-form { margin: 20px; }
        .user-list { margin-bottom: 20px; }
        .user-item { margin: 5px 0; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .restore-button { background-color: #FFA500; }
        .restore-button:hover { background-color: #FF8C00; }
        .update-role-form { margin-top: 20px; }
        .update-role-form select { padding: 5px; }
    </style>
</head>
<body>

<h2>Felhasználók kezelése</h2>

<!-- Felhasználók listázása -->
<form method="post" class="user-management-form">
    <h3>Aktív Felhasználók:</h3>
    <div class="user-list">
        <?php foreach ($users as $user): ?>
            <?php
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            ?>
            <div class="user-item">
                <input type="checkbox" name="selected_users[]" value="<?php echo $savedUsername; ?>"> 
                <?php echo $savedUsername . " - " . implode(', ', explode(',', $savedRoles)); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" name="delete_users">Törlés</button>
</form>

<h3>Törölt Felhasználók:</h3>
<div class="user-list">
    <?php foreach ($deletedUsers as $deletedUser): ?>
        <?php
        list($deletedUsername, $deletedPassword, $deletedRoles) = explode('|', $deletedUser);
        ?>
        <div class="user-item">
            <?php echo $deletedUsername . " - " . implode(', ', explode(',', $deletedRoles)); ?>
            <form method="post" style="display:inline;">
                <button type="submit" name="restore_user" value="<?php echo $deletedUsername; ?>" class="restore-button">Visszaállítás</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<h3>Rang módosítása:</h3>
<form method="post" class="update-role-form">
    <label for="username_to_update">Felhasználónév:</label>
    <select name="username_to_update" required>
        <option value="" disabled selected>Válassz felhasználót</option>
        <?php foreach ($users as $user): ?>
            <?php
            list($savedUsername, $savedPassword, $savedRoles) = explode('|', $user);
            ?>
            <option value="<?php echo $savedUsername; ?>"><?php echo $savedUsername; ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="new_roles">Új rang(ok):</label><br>
    <input type="checkbox" name="new_roles[]" value="felhasználó"> Felhasználó<br>
    <input type="checkbox" name="new_roles[]" value="premium"> Prémium<br>
    <input type="checkbox" name="new_roles[]" value="admin"> Admin<br><br>

    <button type="submit" name="update_role">Rang módosítása</button>
</form>

</body>
</html>
