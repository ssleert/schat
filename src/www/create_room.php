<?php
session_start();

if (!isset($_SESSION["user_nickname"])) {
    header("Location: set_nickname.php");
    exit;
}

$db_path = "../../chat_app.db";
$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $room_name = trim($_POST["room_name"] ?? "");

    if (!$room_name) {
        $error = "Room name is required.";
    } elseif (strlen($room_name) > 100) {
        $error = "Room name must be 100 characters or less.";
    } else {
        $db = new SQLite3($db_path);
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE nickname = :nickname");
            $stmt->bindValue(":nickname", $_SESSION["user_nickname"], SQLITE3_TEXT);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);

            if ($user) {
                $stmt = $db->prepare("INSERT OR IGNORE INTO rooms (name, creator_id) VALUES (:name, :creator_id)");
                $stmt->bindValue(":name", $room_name, SQLITE3_TEXT);
                $stmt->bindValue(":creator_id", $user["id"], SQLITE3_INTEGER);
                $stmt->execute();

                $success = $db->changes() > 0;
                if (!$success) {
                    $error = "Room name already exists.";
                }
            } else {
                $error = "User not found.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        } finally {
            $db->close();
        }
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
  }
  h1 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 2rem;
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
    width: 24rem;
    margin: 0 auto;
  }
  .input {
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.375rem;
    padding: 0.5rem;
    width: 100%;
    color: #ffffff;
    margin-bottom: 1rem;
  }
  .error {
    color: #f87171;
    margin-bottom: 1rem;
  }
  .success-message {
    font-size: 1.25rem;
    margin-bottom: 2rem;
  }
</style>

<body>
<div class="container">
    <?php if ($success): ?>
        <h1>Room Created</h1>
        <div class="success-message">
            Room "<?= htmlspecialchars($room_name) ?>" has been created.
        </div>
        <a href="index.php" class="btn">Go to Chat Rooms</a>
    <?php else: ?>
        <h1>Create a Chat Room</h1>
        <div class="form-container">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="create_room.php">
                <input type="text" name="room_name" class="input" placeholder="Enter room name" maxlength="100" required>
                <button type="submit" class="btn">Create Room</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>

</html>
