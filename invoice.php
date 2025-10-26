<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
$uid = $_SESSION['user_id'];

// list payments
$payments = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC");
$payments->execute([$uid]);
$payments = $payments->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Invoices - MatangReads</title>
<link rel="stylesheet" href="/matangreads/css/style.css">
</head><body>
<?php include 'navbar.php'; ?>

<div class="container" style="max-width: 900px; margin: 40px auto;">
<div class="form-container"> 
  <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 8px; margin-bottom: 20px;">Your Payments & Invoices</h2>
  
  <?php if(!$payments): ?>
    <p style="text-align: center; color: #555;">No payments recorded yet.</p>
  <?php else: ?>
  
  <table class="simple-table" style="margin-bottom: 25px;">
    <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Description</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach($payments as $p): ?>
        <tr>
          <td>#<?php echo $p['payment_id'];?></td>
          <td>RM <?php echo number_format($p['amount'],2);?></td>
          <td><?php echo htmlspecialchars($p['payment_method']);?></td>
          <td><?php echo htmlspecialchars($p['description']);?></td>
          <td><?php echo $p['payment_date'];?></td>
          <td>
            <a href="receipt.php?id=<?php echo $p['payment_id'];?>" style="color: #0056b3; font-weight: bold;">View/Download</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div style="text-align: center;">
    <a href="/matangreads/pay.php" class="btn" style="background: #2d0115; font-size: 16px;">Make New Payment</a>
  </div>
</div>
</div>
</body></html>
