<?php
require_once 'config.php';
session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>MatangReads Library</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/homepage.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="homepage">
<?php include 'navbar.php'; ?>

<section class="about">
    <h1>📚 Selamat Datang to MatangReads Library</h1>
    <p>
      MatangReads is a digital library system. <br><br>
      🔎 Easy Book Search <br> 📖 Wide Selection <br> ⏱ Quick Borrow & Request <br>💻 24/7 Digital Access
      <br><br> Start exploring thousands of books today. Borrow, read, and grow with MatangReads.
    </p>
</section>

<!-- The slideshow must be BELOW the about section -->
<section class="slideshow">
  <div class="slideshow-container">
    <?php
    // fetch a few images from DB
    $stmt = $pdo->query("SELECT image, bookname FROM books LIMIT 8");
    while ($row = $stmt->fetch()) {
        $img = htmlspecialchars($row['image']);
        echo "<img src=\"/matangreads/Images/{$img}\" alt=\"".htmlspecialchars($row['bookname'])."\">";
    }
    ?>
  </div>
</section>

<div class="view-more">
  <a href="user/books.php" class="btn">View More Books</a>
</div>

<footer>
  <p>© <?php echo date('Y');?> MatangReads Library</p>
</footer>

</body>
</html>
