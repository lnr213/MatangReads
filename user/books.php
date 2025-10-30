<?php
require_once '../config.php';
session_start();
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$params = [];
$sql = "SELECT * FROM books WHERE 1=1";
if ($q) {
    $sql .= " AND (bookname LIKE ? OR author LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

// categories for filter
$cats = $pdo->query("SELECT DISTINCT category FROM books")->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Books - MatangReads</title>
<link rel="stylesheet" href="../css/style.css">
</head><body>
<?php include '../navbar.php'; ?>

<div class="container">
  <h2 style="color: #ffffffff;">Books</h2>
  <form method="get" class="search-inline">
    <input name="q" placeholder="Search by title or author" value="<?php echo htmlspecialchars($q); ?>">
    <select name="category">
      <option value="">All categories</option>
      <?php foreach($cats as $c): ?>
        <option <?php if($c==$category) echo 'selected';?>><?php echo htmlspecialchars($c);?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Search</button>
  </form>

  <div class="books-box">
  <?php foreach($books as $b): ?>
    <div class="book-card">
      <img src="../Images/<?php echo htmlspecialchars($b['image']);?>" alt="">
      <h4><?php echo htmlspecialchars($b['bookname']);?></h4>
      <p><?php echo htmlspecialchars($b['author']);?></p>
      <p>Category: <?php echo htmlspecialchars($b['category']);?></p>
      <p>Available: <?php echo (int)$b['quantity']; ?></p>
      <a class="btn" href="book_details.php?id=<?php echo $b['book_id']; ?>">Details</a>
    </div>
  <?php endforeach; ?>
  </div>
</div>
</body></html>
