<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

// stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalBorrowed = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status='approved'")->fetchColumn(); // Only count approved/out
$totalAvailable = $pdo->query("SELECT SUM(quantity) FROM books")->fetchColumn();
$bookRequests = $pdo->query("SELECT COUNT(*) FROM book_requests WHERE status='pending'")->fetchColumn();
$overdue = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE due_date < CURDATE() AND status='approved'")->fetchColumn();
$pendingReservations = $pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status='pending'")->fetchColumn();
$totalPayments = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn();
?>

<!doctype html>
<html><head><meta charset="utf-8"><title>Admin Home</title>
<link rel="stylesheet" href="../css/style.css"> 
<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 


</head>
<body>
<?php include '../navbar.php'; ?>

<div class="admin-grid">
  <div class="section-header">
    <h2 style="color: white;">System Overview</h2>
  </div>

  <div class="stats">
    <div class="card">
      <i class="fas fa-users"></i>
      Total Users<br><strong><?php echo $totalUsers; ?></strong>
    </div>
    <div class="card">
      <i class="fas fa-book"></i>
      Active Borrows<br><strong><?php echo $totalBorrowed; ?></strong>
    </div>
    <div class="card" style="background-color: #ffdddd; color: #800;">
      <i class="fas fa-clock"></i>
      Overdue Books<br><strong><?php echo $overdue; ?></strong>
    </div>
    <div class="card">
      <i class="fas fa-cubes"></i>
      Total Stock<br><strong><?php echo $totalAvailable ?: 0; ?></strong>
    </div>
    <div class="card" style="background-color: #fff9e6;">
      <i class="fas fa-hand-paper"></i>
      Pending Reservations<br><strong><?php echo $pendingReservations; ?></strong>
    </div>
    <div class="card" style="background-color: #fff9e6;">
      <i class="fas fa-question-circle"></i>
      Pending Book Requests<br><strong><?php echo $bookRequests; ?></strong>
    </div>
    <div class="card">
      <i class="fas fa-money-bill-wave"></i>
      Payments (RM)<br><strong><?php echo number_format($totalPayments ?: 0, 2); ?></strong>
    </div>
  </div>

  <div class="section-header">
    <h2 style="color: white;">Action Center</h2>
  </div>
  
  <div class="action-center" style="display: flex; gap: 20px; padding: 20px 0; flex-wrap: wrap;">
    <a href="manage_reservations.php" class="btn" style="background-color: #2d0115; color: white;">
        <i class="fas fa-calendar-check"></i> Manage Reservations (<?php echo $pendingReservations; ?> Pending)
    </a>
    <a href="manage_book_requests.php" class="btn" style="background-color: #AE8625; color: #2d0115;">
        <i class="fas fa-list-alt"></i> Manage Book Requests (<?php echo $bookRequests; ?> Pending)
    </a>
    <a href="manage_books.php" class="btn" style="background-color: #5f042f; color: white;">
        <i class="fas fa-books"></i> Manage Book Catalog
    </a>
  </div>

</div>
</body>
</html>
