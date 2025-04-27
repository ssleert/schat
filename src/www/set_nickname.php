<?php
session_start();

$db_path = "../../chat_app.db";
$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nickname = trim($_POST["nickname"] ?? '');

    if ($nickname === '') {
        $error = "Nickname is required.";
    } elseif (mb_strlen($nickname) > 50) {
        $error = "Nickname must be 50 characters or less.";
    } else {
        try {
            $db = new SQLite3($db_path, SQLITE3_OPEN_READWRITE);
            $stmt = $db->prepare("INSERT OR IGNORE INTO users (nickname) VALUES (:nickname)");
            $stmt->bindValue(":nickname", $nickname, SQLITE3_TEXT);
            $stmt->execute();

            if ($db->changes() > 0) {
                $_SESSION["user_nickname"] = $nickname;
                $success = true;
            } else {
                $error = "Nickname already taken.";
            }
        } catch (Exception $e) {
            $error = "Database error. Please try again later.";
            error_log($e->getMessage());
        } finally {
            if (isset($db)) {
                $db->close();
            }
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
      <h1>Nickname Set</h1>
      <div class="success-message">
        Your nickname "<?= htmlspecialchars($_SESSION["user_nickname"], ENT_QUOTES, 'UTF-8') ?>" has been set.
      </div>
      <a href="index.php" class="btn">Go to Chat Rooms</a>
    <?php else: ?>
      <h1>Set Your Nickname</h1>
      <div class="form-container">
        <?php if ($error): ?>
          <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST" action="set_nickname.php" novalidate>
          <input type="text" name="nickname" class="input" placeholder="Enter your nickname" maxlength="50" required>
          <button type="submit" class="btn">Set Nickname</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
