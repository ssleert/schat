<?php
session_start();

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: set_nickname.php");
    exit;
}

if (!isset($_SESSION["user_nickname"])) {
    header("Location: set_nickname.php");
    exit;
}

$db_path = "../../chat_app.db";
$rooms = [];

try {
    $db = new SQLite3($db_path);

    $query = "
        SELECT r.id, r.name, u.nickname as creator_nickname
        FROM rooms r
        LEFT JOIN users u ON r.creator_id = u.id
        ORDER BY r.created_at DESC
    ";
    $result = $db->query($query);

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rooms[] = $row;
    }
    $db->close();
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
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
  .container { text-align: center; }
  h1 { font-size: 1.875rem; font-weight: 700; margin-bottom: 2rem; }
  .btn, .logout-btn {
    background-color: #1f2937;
    border: 1px solid #374151;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    color: #ffffff;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s ease-in-out;
    margin: 0.25rem;
    cursor: pointer;
  }
  .btn:hover, .logout-btn:hover { background-color: #374151; }
  .logout-btn { border: 1px solid #374151; }
  .room-list-container { width: 24rem; margin: 0 auto; }
  h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; }
  .room-list { margin-bottom: 1.5rem; }
  .room-item {
    display: block;
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    text-decoration: none;
    color: #ffffff;
    transition: background-color 0.2s ease-in-out;
  }
  .room-item:hover { background-color: #374151; }
  .error { color: #f87171; margin-bottom: 1rem; }
  .room-item-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
  }
  .delete-btn {
    background-color: #991b1b;
    border: 1px solid #7f1d1d;
  }
  .delete-btn:hover { background-color: #7f1d1d; }
  .inline-form { display: inline; }
</style>

<body>
  <div class="container">
    <h1>Chat Rooms</h1>
    <div class="room-list">
      <a href="create_room.php" class="btn">Create New Room</a>
      <a href="set_nickname.php" class="btn">Set Nickname</a>
      <form method="POST" action="index.php" class="inline-form">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
      </form>
    </div>

    <div class="room-list-container">
      <h2>Available Rooms</h2>
      <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div class="room-list">
        <?php if (empty($rooms)): ?>
          <div class="room-item">No rooms available.</div>
        <?php else: ?>
          <?php foreach ($rooms as $room): ?>
            <div class="room-item-wrapper">
              <a href="room.php?id=<?= $room["id"] ?>" class="room-item"><?= htmlspecialchars($room["name"]) ?></a>
              <?php if ($room["creator_nickname"] === $_SESSION["user_nickname"]): ?>
                <a href="remove_room.php?id=<?= $room["id"] ?>" class="btn delete-btn">Delete</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
