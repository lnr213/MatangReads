<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$uid = $_SESSION['user_id'];
$payment_id = (int)($_GET['id'] ?? 0);

// Fetch the payment record, ensuring it belongs to the logged-in user
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name, u.email
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.payment_id = ? AND p.user_id = ?
");
$stmt->execute([$payment_id, $uid]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: invoice.php'); // Redirect if not found or unauthorized
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Receipt #<?php echo $payment_id; ?> - MatangReads</title>
<link rel="stylesheet" href="css/style.css">
<style>
/* Style for the receipt block */
.receipt-box {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-width: 600px;
    margin: 40px auto;
    text-align: left;
}
.receipt-header {
    border-bottom: 2px solid #AE8625;
    padding-bottom: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.receipt-header h2 {
    color: #2d0115;
    margin: 0;
}
.receipt-details p {
    margin: 8px 0;
    padding: 4px 0;
    border-bottom: 1px dotted #eee;
}
.receipt-total {
    margin-top: 20px;
    padding: 15px;
    background: #f7ef8a;
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    border-radius: 4px;
}
.no-print {
    text-align: center;
    margin-top: 20px;
}

/* Print media query for clean download */
@media print {
    .navbar, .no-print {
        display: none;
    }
    .receipt-box {
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: none;
    }
}
</style>
</head><body>
<?php include 'navbar.php'; ?>

<div class="receipt-box">
    <div class="receipt-header">
        <h2>MatangReads Digital Receipt</h2>
        <p>Transaction ID: **#<?php echo $payment['payment_id']; ?>**</p>
    </div>
    
    <div class="receipt-details">
        <p><strong>Date & Time:</strong> <?php echo date('F j, Y, g:i a', strtotime($payment['payment_date'])); ?></p>
        <p><strong>Paid By:</strong> <?php echo htmlspecialchars($payment['full_name']); ?> (<?php echo htmlspecialchars($payment['email']); ?>)</p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($payment['description']); ?></p>
    </div>
    
    <div class="receipt-total">
        Amount Paid: RM <?php echo number_format($payment['amount'], 2); ?>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()" class="btn" style="background-color: #2d0115; color: white; margin-right: 10px;">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
        <a href="dashboard.php" class="btn" style="background-color: #AE8625; color: #2d0115;">
            Back to Dashboard
        </a>
    </div>
</div>

</body></html>
