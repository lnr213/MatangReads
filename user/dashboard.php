<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 1. Fetch User Data (including credit balance)
$user_stmt = $pdo->prepare("SELECT username, full_name, profile_pic, credit_balance FROM users WHERE user_id = ?");
$user_stmt->execute([$userId]);
$user_data = $user_stmt->fetch();
$credit_balance = (float)($user_data['credit_balance'] ?? 0.00);

// --- Handle Cancellation Request (UX Enhancement) ---
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $cancel_id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM borrow_requests WHERE borrow_id = ? AND user_id = ? AND status = 'pending'")
        ->execute([$cancel_id, $userId]);
    header('Location: dashboard.php');
    exit;
}
// --------------------------------------------------

// 2. Fetch User Reservations (Borrowed and Requested)
$stmt_res = $pdo->prepare("
    SELECT br.*, b.bookname, b.author 
    FROM borrow_requests br 
    JOIN books b ON br.book_id = b.book_id 
    WHERE br.user_id = ? 
    ORDER BY br.created_at DESC
");
$stmt_res->execute([$userId]);
$reservations = $stmt_res->fetchAll();

// 3. Calculate Total Outstanding Fine
$total_fine = 0.00;
foreach ($reservations as $r) {
    if ($r['status'] === 'approved' && $r['due_date'] < date('Y-m-d')) {
        $fine = calculate_fine($r['due_date']);
        $total_fine += $fine;
    }
}

// 4. Net Amount Due (Fine minus Credit)
$net_amount_due = max(0.00, $total_fine - $credit_balance);

// 5. Featured books
$stmt = $pdo->query("SELECT * FROM books ORDER BY book_id DESC LIMIT 6");
$books = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard - MatangReads</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container">
<h2 style="color: #ffffffff;">Welcome back, <?php echo htmlspecialchars($username); ?></h2>

<section class="user-summary" style="margin-bottom: 30px;">
<p>
    <span style="font-weight: bold; color: green;"><i class="fas fa-wallet"></i> Your Available Credit:</span> 
    RM <?php echo number_format($credit_balance, 2); ?>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <span style="color: #ffffffff; font-weight: bold;"><i class="fas fa-coins"></i> Daily Fine Rate:</span> 
    RM <?php echo number_format(DAILY_FINE_RATE, 2); ?> per day after due date.
</p>
</section>

<section class="user-activity">
<h3 style="color: #ffffffff; border-bottom: 2px solid #AE8625; padding-bottom: 5px; margin-bottom: 15px;">Your Borrowing Activity</h3>
<?php if (empty($reservations)): ?>
<p>You have no current or past reservations. <a href="books.php">Start browsing now!</a></p>
<?php else: ?>
<table class="simple-table">
<thead>
<tr>
<th>Book</th>
<th>Status</th>
<th>Borrow Date</th>
<th>Due Date</th>
<th>Fine (RM)</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php foreach ($reservations as $r): ?>
<?php
$fine = 0.00;
$due_date = $r['due_date'];
$status = $r['status'];
$action_link = '-';

// fine calculation
if ($status === 'approved' && $due_date < date('Y-m-d')) {
    $fine = calculate_fine($due_date);
}
if ($status === 'returned') {
    $fine = $r['fine'];
}

$fine_style = $fine > 0 ? 'style="color:red; font-weight:bold;"' : 'style="color:green;"';

if ($status === 'pending') {
    $action_link = '<a href="dashboard.php?action=cancel&id=' . $r['borrow_id'] . '" onclick="return confirm(\'Are you sure you want to cancel this request?\')" style="color: red; font-weight: bold;">Cancel</a>';
}
?>
<tr>
<td><?php echo htmlspecialchars($r['bookname']) . ' (' . htmlspecialchars($r['author']) . ')'; ?></td>
<td><span class="status-<?php echo $status; ?>"><?php echo ucfirst($status); ?></span></td>
<td><?php echo $r['borrow_date']; ?></td>
<td><?php echo $due_date; ?></td>
<td <?php echo $fine_style; ?>><?php echo number_format($fine, 2); ?></td>
<td><?php echo $action_link; ?></td>
</tr>
<?php endforeach; ?>

<tr>
<td colspan="4" style="text-align:right; font-weight:bold; background:#f0f0f0;">Total Outstanding Fines:</td>
<td style="color:red; font-weight:bold; background:#f0f0f0;">RM <?php echo number_format($total_fine, 2); ?></td>
<td style="background:#f0f0f0;"></td>
</tr>

<?php if ($credit_balance > 0.00): ?>
<tr>
<td colspan="4" style="text-align:right; font-weight:bold; background:#e0ffe0;">Applied Credit:</td>
<td style="color:green; font-weight:bold; background:#e0ffe0;">- RM <?php echo number_format($credit_balance, 2); ?></td>
<td style="background:#e0ffe0;"></td>
</tr>
<?php endif; ?>

<tr>
<td colspan="4" style="text-align:right; font-weight:bold; background:#cceeff;">NET AMOUNT DUE:</td>
<td style="color:<?php echo $net_amount_due > 0 ? 'red' : 'green'; ?>; font-weight:bold; background:#cceeff;">
RM <?php echo number_format($net_amount_due, 2); ?>
</td>
<td style="background:#cceeff;"></td>
</tr>

</tbody>
</table>

<div style="text-align: right; margin-top: 15px;">
<a class="btn" href="pay.php?amount=<?php echo $net_amount_due; ?>">
<?php echo $net_amount_due > 0 ? 'Pay Outstanding Fees' : 'Make Optional Payment'; ?> 
(RM <?php echo number_format($net_amount_due, 2); ?>)
</a>
<a class="btn" href="invoice.php" style="background-color: #AE8625; margin-left: 10px;">View Payment History</a>
</div>
<?php endif; ?>
</section>

<section class="featured" style="margin-top: 40px;">
<h3 style="color: #ffffffff; border-bottom: 2px solid #AE8625; padding-bottom: 5px; margin-bottom: 15px;">Featured Books</h3>
<div class="books-box">
<?php foreach ($books as $b): ?>
<div class="book-card">
<img src="../Images/<?php echo htmlspecialchars($b['image']); ?>" alt="<?php echo htmlspecialchars($b['bookname']); ?>">
<h4><?php echo htmlspecialchars($b['bookname']); ?></h4>
<p><?php echo htmlspecialchars($b['author']); ?></p>
<a class="btn" href="book_details.php?id=<?php echo $b['book_id']; ?>">Details</a>
</div>
<?php endforeach; ?>
<?php if (empty($books)): ?>
<p style="text-align: center;">No featured books available.</p>
<?php endif; ?>
</div>
</section>
</div>
</body>
</html>
