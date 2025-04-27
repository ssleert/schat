<?php
session_start();

if (empty($_SESSION["user_nickname"])) {
    header("Location: set_nickname.php");
    exit;
}

$db_path = "../../chat_app.db";
$error = "";
$room = null;
$is_creator = false;

$room_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$room_id) {
    $error = "Invalid room ID.";
} else {
    try {
        $db = new SQLite3($db_path);

        $stmt = $db->prepare("
            SELECT r.id, r.name, r.creator_id, u.nickname
            FROM rooms r
            JOIN users u ON r.creator_id = u.id
            WHERE r.id = :id
        ");
        $stmt->bindValue(":id", $room_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $room = $result->fetchArray(SQLITE3_ASSOC);

        if (!$room) {
            $error = "Room not found.";
        } else {
            $is_creator = $room["nickname"] === $_SESSION["user_nickname"];
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error && $is_creator) {
            $deleteStmt = $db->prepare("DELETE FROM rooms WHERE id = :id");
            $deleteStmt->bindValue(":id", $room_id, SQLITE3_INTEGER);
            $deleteStmt->execute();

            if ($db->changes() > 0) {
                $db->close();
                header("Location: index.php");
                exit;
            } else {
                $error = "Failed to delete room.";
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
        width: 24rem;
        margin: 0 auto;
    }
    h1 {
        font-size: 1.875rem;
        font-weight: 700;
        margin-bottom: 2rem;
    }
    .btn, .delete-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        color: #ffffff;
        text-decoration: none;
        display: inline-block;
        margin: 0.25rem;
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
        border: 1px solid;
    }
    .btn {
        background-color: #1f2937;
        border-color: #374151;
    }
    .btn:hover {
        background-color: #374151;
    }
    .delete-btn {
        background-color: #991b1b;
        border-color: #7f1d1d;
    }
    .delete-btn:hover {
        background-color: #7f1d1d;
    }
    .error {
        color: #f87171;
        margin-bottom: 1rem;
    }
    .confirm-message {
        font-size: 1.25rem;
        margin-bottom: 2rem;
    }
</style>
<body>
<div class="container">
    <?php if ($error): ?>
        <h1>Error</h1>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <a href="index.php" class="btn">Back to Chat Rooms</a>
    <?php elseif (!$is_creator): ?>
        <h1>Unauthorized</h1>
        <div class="error">You are not the creator of this room.</div>
        <a href="index.php" class="btn">Back to Chat Rooms</a>
    <?php else: ?>
        <h1>Delete Room</h1>
        <div class="confirm-message">
            Are you sure you want to delete "<?= htmlspecialchars($room["name"]) ?>"?
        </div>
        <form method="POST" action="remove_room.php?id=<?= $room["id"] ?>">
            <button type="submit" class="delete-btn">Delete Room</button>
            <a href="index.php" class="btn">Cancel</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
