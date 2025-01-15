<?php
session_start();

// Bejelentkezés ellenőrzése
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Üzenetküldés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = strip_tags(trim($_POST['message']));

    if (!empty($message)) {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "$timestamp - $username ($role): $message\n";

        file_put_contents('messages.txt', $entry, FILE_APPEND);

        header('Location: ' . $_SERVER['PHP_SELF'] . '?message_sent=1');
        exit();
    }
}

// Üzenetek betöltése
$messages = file_exists('messages.txt') ? file_get_contents('messages.txt') : '';

// Üzenet elküldésének ellenőrzése
$message_sent = isset($_GET['message_sent']) && $_GET['message_sent'] == 1;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .chat-box {
            width: 95%;
            max-width: 1200px;
            margin: 10px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .chat-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .messages {
            border: 1px solid #ddd;
            padding: 10px;
            height: 400px;
            overflow-y: auto;
            background-color: #f9f9f9;
            margin-bottom: 10px;
            border-radius: 5px;
            scrollbar-width: thin;
            scrollbar-color: #aaa #eee;
        }

        .messages::-webkit-scrollbar {
            width: 8px;
        }

        .messages::-webkit-scrollbar-track {
            background: #eee;
        }

        .messages::-webkit-scrollbar-thumb {
            background-color: #aaa;
            border-radius: 4px;
        }

        .message {
            padding: 8px;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .message:last-child {
            border-bottom: none;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;
            resize: vertical;
        }

        .input-area {
            display: flex;
            flex-direction: column;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 5px;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            display: inline-block;
            padding: 8px 16px;
            margin-top: 10px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #0056b3;
        }

        .button {
            background-color: #dc3545;
        }

        .button:hover{
            background-color: #bd2130;
        }

        @media (max-width: 768px) {
            .chat-box {
                width: 98%;
                max-width: 100%;
                padding: 10px;
            }
        }
    </style>
    <script>
        function refreshChat() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "load_messages.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const chatMessages = document.getElementById("chat-messages");
                    const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 1;

                    document.getElementById("chat-messages").innerHTML = xhr.responseText;

                    if (isScrolledToBottom) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }
            };
            xhr.send();
        }

        setInterval(refreshChat, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('textarea[name="message"]');
            const form = textarea.closest('form');
            const chatMessages = document.getElementById("chat-messages");

            textarea.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    form.submit();
                }
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    </script>
</head>
<body>
    <div class="chat-box">
        <h2>Global Chat</h2>

        <div class="messages" id="chat-messages">
            <?php if (!empty($messages)) echo nl2br(htmlspecialchars($messages)); ?>
        </div>

        <div class="input-area">
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <textarea name="message" rows="3" placeholder="Írd ide az üzeneted..."></textarea><br>
                <button type="submit">Üzenet küldése</button>
            </form>
        </div>

        <?php if ($message_sent): ?>
            <p style="color: green;">Üzenet sikeresen elküldve!</p>
        <?php endif; ?>

        <div style="margin-top: auto;">
            <a href="logout.php">Kijelentkezés</a>
            <a href="login.php" class="button">Vissza a főoldalra</a>
        </div>
    </div>
</body>
</html>

