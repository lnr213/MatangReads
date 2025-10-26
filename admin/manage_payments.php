<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, u.username, u.full_name, u.email FROM payments p 
        LEFT JOIN users u ON p.user_id = u.user_id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Payments & Invoices</title>
<link rel="stylesheet" href="/matangreads/css/style.css">
<link rel="stylesheet" href="/matangreads/css/admin.css">
</head><body>
<?php include '../navbar.php'; ?>
<div class="admin-grid">
<div class="admin-content">
  <h2>Payment & Invoice Management</h2>
  
  <form method="get" class="search-inline" style="margin-bottom: 15px;">
    <input name="search" placeholder="Search by user or description" value="<?php echo htmlspecialchars($search); ?>" style="width: 50%;">
    <button type="submit" class="btn" style="background-color: #AE8625; color: #2d0115;">Search</button>
  </form>
  
  <table class="simple-table">
    <thead><tr><th>ID</th><th>User (Name)</th><th>Username</th><th>Amount</th><th>Method</th><th>Description</th><th>Date</th></tr></thead>
    <tbody>
      <?php foreach($payments as $p): ?>
        <tr>
          <td><?php echo $p['payment_id'];?></td>
          <td><?php echo htmlspecialchars($p['full_name'] ?: 'N/A');?></td>
          <td><?php echo htmlspecialchars($p['username']);?></td>
          <td>RM **<?php echo number_format($p['amount'],2);?>**</td>
          <td><?php echo htmlspecialchars($p['payment_method']);?></td>
          <td><?php echo htmlspecialchars($p['description']);?></td>
          <td><?php echo date('Y-m-d H:i', strtotime($p['payment_date']));?></td>
        </tr>
      <?php endforeach;?>
      <?php if (empty($payments)): ?>
          <tr><td colspan="7" style="text-align:center;">No payment records found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</div>
</body></html>
