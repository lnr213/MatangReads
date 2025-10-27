<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['book_title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    if ($title) {
        $ins = $pdo->prepare("INSERT INTO book_requests (user_id, book_title, author, category, notes) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$_SESSION['user_id'], $title, $author, $category, $notes]);
        $msg = 'Request submitted.';
    } else {
        $msg = 'Title required.';
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Request Book - MatangReads</title>
<link rel="stylesheet" href="css/style.css">
</head><body>
<?php include 'navbar.php'; ?>
<div class="form-container">
  <h2>Request a new book</h2>
  <?php if($msg) echo "<p class='success'>".htmlspecialchars($msg)."</p>"; ?>
  <form method="post">
    <label>Book title</label><input name="book_title" required>
    <label>Author</label><input name="author">
    <label>Category</label><input name="category">
    <label>Notes</label><textarea name="notes"></textarea>
    <div class="form-buttons"><button type="submit">Send request</button></div>
  </form>
</div>
</body>
</html>
