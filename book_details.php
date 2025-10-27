<?php
require_once 'config.php';
session_start();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM books WHERE book_id=?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { header('Location: books.php'); exit; }
?>
<!doctype html>
<html><head><meta charset="utf-8"><title><?php echo htmlspecialchars($book['bookname']); ?> - MatangReads</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/book_details.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
  <div class="book-detail">
  <img src="/matangreads/Images/<?php echo htmlspecialchars($book['image']);?>" alt="">
  
  <div class="book-info">
      <h2><?php echo htmlspecialchars($book['bookname']);?></h2>
      <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']);?></p>
      <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']);?></p>
      <p><?php echo nl2br(htmlspecialchars($book['description']));?></p>
      <p><strong>Stock:</strong> <?php echo (int)$book['quantity'];?></p>

      <?php if(isset($_SESSION['user_id'])): ?>
        <a class="btn" href="/matangreads/borrow.php?id=<?php echo $book['book_id']; ?>">Reserve / Borrow</a>
      <?php else: ?>
        <p><a href="/matangreads/login.php">Login</a> to reserve</p>
      <?php endif; ?>
  </div>
</div>

</div>

</body></html>
