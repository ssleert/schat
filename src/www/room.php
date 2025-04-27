<?php
session_start();

if (empty($_SESSION["user_nickname"])) {
    header("Location: set_nickname.php");
    exit;
}

$db_path = "../../chat_app.db";
$error = "";
$room = null;
$messages = [];

$room_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$room_id) {
    $error = "Invalid room ID.";
} else {
    try {
        $db = new SQLite3($db_path);

        $stmt = $db->prepare("SELECT id, name FROM rooms WHERE id = :id");
        $stmt->bindValue(":id", $room_id, SQLITE3_INTEGER);
        $room = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if (!$room) {
            $error = "Room not found.";
        } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
            $content = trim($_POST["content"] ?? "");

            if ($content === "") {
                $error = "Message cannot be empty.";
            } elseif (mb_strlen($content) > 1000) {
                $error = "Message must be 1000 characters or less.";
            } else {
                $stmt = $db->prepare("SELECT id FROM users WHERE nickname = :nickname");
                $stmt->bindValue(":nickname", $_SESSION["user_nickname"], SQLITE3_TEXT);
                $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

                if ($user) {
                    $stmt = $db->prepare("INSERT INTO messages (room_id, user_id, content) VALUES (:room_id, :user_id, :content)");
                    $stmt->bindValue(":room_id", $room_id, SQLITE3_INTEGER);
                    $stmt->bindValue(":user_id", $user["id"], SQLITE3_INTEGER);
                    $stmt->bindValue(":content", $content, SQLITE3_TEXT);
                    $stmt->execute();

                    header("Location: room.php?id=$room_id");
                    exit;
                } else {
                    $error = "User not found.";
                }
            }
        }

        if (!$error) {
            $stmt = $db->prepare("
                SELECT m.content, m.sent_at, u.nickname
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.room_id = :room_id
                ORDER BY m.sent_at ASC
            ");
            $stmt->bindValue(":room_id", $room_id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $messages[] = $row;
            }
        }

        $db->close();
    } catch (Exception $e) {
        $error = "Database error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include "./components/head.php"; ?>

<style>
  body {
    background-color: #111827;
    color: #ffffff;
    font-family: "Inter", sans-serif;
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .container {
    text-align: center;
    width: 28rem;
    margin: 0 auto;
  }
  h1 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 1rem;
  }
  .btn {
    background-color: #1f2937;
    border: 1px solid #374151;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    color: #ffffff;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s ease-in-out;
    margin: 0.25rem;
  }
  .btn:hover {
    background-color: #374151;
  }
  .form-container {
    margin-bottom: 1rem;
  }
  .input {
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.375rem;
    padding: 0.5rem;
    width: 100%;
    color: #ffffff;
    margin-bottom: 0.5rem;
  }
  .error {
    color: #f87171;
    margin-bottom: 1rem;
  }
  .message-list {
    max-height: 50vh;
    overflow-y: auto;
    margin-bottom: 1rem;
    border: 1px solid #374151;
    border-radius: 0.375rem;
    padding: 0.5rem;
  }
  .message {
    text-align: left;
    padding: 0.5rem;
    border-bottom: 1px solid #374151;
  }
  .message:last-child {
    border-bottom: none;
  }
  .message-meta {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 0.25rem;
  }

</style>

<body>
<div class="container">
    <?php if ($error): ?>
        <h1>Error</h1>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <a href="index.php" class="btn">Back to Chat Rooms</a>

    <?php elseif ($room): ?>
        <h1><?= htmlspecialchars($room["name"]) ?></h1>

        <div class="message-list">
            <?php if (empty($messages)): ?>
                <div class="message">No messages yet.</div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message">
                        <div class="message-meta">
                            <?= htmlspecialchars($message["nickname"]) ?> • <?= date("Y-m-d H:i", strtotime($message["sent_at"])) ?>
                        </div>
                        <?= nl2br(htmlspecialchars($message["content"])) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-container">
            <form method="POST" action="room.php?id=<?= $room["id"] ?>">
                <input type="text" name="content" class="input" placeholder="Type your message" maxlength="1000" required>
                <button type="submit" class="btn">Send</button>
                <a href="room.php?id=<?= $room["id"] ?>" class="btn">Refresh</a>
            </form>
        </div>

        <a href="index.php" class="btn">Back to Chat Rooms</a>
    <?php endif; ?>
</div>
</body>
</html>
