<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$userId = $_SESSION['user_id'];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['book_title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($title)) {
        $success_msg = 'Error: Book title is required.';
    } else {
        $ins = $pdo->prepare("INSERT INTO book_requests (user_id, book_title, author, category, notes) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$userId, $title, $author, $cat, $notes]);
        $success_msg = 'Success: Your request for "' . htmlspecialchars($title) . '" has been submitted!';
        
        // Clear POST to allow re-submission and clear form
        $_POST = [];
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Request Book - MatangReads</title>
<link rel="stylesheet" href="../css/style.css">
</head><body>
<?php include '../navbar.php'; ?>

<div class="container">
    <div class="form-container" style="max-width: 600px; margin: 40px auto;">
        <h2>Request a new book</h2>
        <p class="info-text">If you can't find a book in our catalog, you can request that we add it!</p>

        <form method="post">
            <label>Book title *</label>
            <input type="text" name="book_title" required 
                   value="<?php echo htmlspecialchars($_POST['book_title'] ?? ''); ?>">

            <label>Author</label>
            <input type="text" name="author" 
                   value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>">

            <label>Category</label>
            <input type="text" name="category" 
                   value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>">

            <label>Notes</label>
            <textarea name="notes" rows="4"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>

            <div class="form-buttons">
                <button type="submit" class="btn">Send request</button>
            </div>
        </form>
    </div>
</div>

<?php if ($success_msg): ?>
<div id="toast" class="toast-notification toast-<?php echo strpos($success_msg, 'Error') !== false ? 'error' : 'success'; ?>">
    <?php echo htmlspecialchars($success_msg); ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }
    });
</script>
<?php endif; ?>

</body></html>
