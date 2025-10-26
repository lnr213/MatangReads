<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
$uid = $_SESSION['user_id'];
$book_id = (int)($_GET['id'] ?? 0);

// Fetch book details
$book = $pdo->prepare("SELECT * FROM books WHERE book_id=?");
$book->execute([$book_id]);
$book = $book->fetch();
if (!$book) { header('Location: books.php'); exit; }

$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_date_str = $_POST['borrow_date'] ?? null;
    
    if (!$borrow_date_str) {
        $errors[] = 'Please choose a borrow date.';
    } else {
        try {
            // Calculate Due Date: 14 days (2 weeks) from borrow date
            $borrow_date = new DateTime($borrow_date_str);
            $due_date = $borrow_date->modify('+14 days')->format('Y-m-d');
            
            // Check if the user already has this book out or pending
            $check = $pdo->prepare("SELECT borrow_id FROM borrow_requests WHERE user_id = ? AND book_id = ? AND status IN ('pending', 'approved')");
            $check->execute([$uid, $book_id]);
            if ($check->fetch()) {
                 $errors[] = 'You already have this book requested or borrowed.';
            }

            if (!$errors) {
                // Insert the reservation request
                $ins = $pdo->prepare("INSERT INTO borrow_requests (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
                $ins->execute([$uid, $book_id, $borrow_date_str, $due_date]);
                
                // Reduce quantity (optimistic lock, admin must approve later)
                // We rely on admin approval to truly reduce quantity
                
                $success_msg = 'Reservation request submitted successfully! Due date automatically set to ' . htmlspecialchars($due_date) . '.';
                // Clear POST data to prevent resubmission
                $_POST = [];
            }
        } catch (Exception $e) {
            $errors[] = 'Invalid date selected.';
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Borrow - MatangReads</title>
<link rel="stylesheet" href="/matangreads/css/style.css">
<link rel="stylesheet" href="/matangreads/css/navbar.css">
</head><body>
<?php include 'navbar.php'; ?>
<div class="container">
<div class="form-container" style="max-width: 500px; margin: 30px auto;">
  <h2>Reserve: <?php echo htmlspecialchars($book['bookname']);?></h2>
  <?php foreach($errors as $e) echo "<p class='error'>".htmlspecialchars($e)."</p>"; ?>
  <?php if ($success_msg) echo "<p class='success'>".htmlspecialchars($success_msg)."</p>"; ?>
  
  <form method="post">
    <label>Borrow date</label>
    <input type="date" name="borrow_date" min="<?php echo date('Y-m-d'); ?>" required>
    
    <p class="info-text">
        Note: The due date will be automatically set to **2 weeks** from the borrow date.
    </p>
    
    <div class="form-buttons">
        <button type="submit" class="btn">Submit request</button>
    </div>
  </form>
</div>
</div>
</body></html>
