<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

// -------------------------------------------------------------
// 1. TOP BORROWED BOOKS (Historical Activity)
// -------------------------------------------------------------
$topBorrowed = $pdo->query("
    SELECT b.bookname, COUNT(br.borrow_id) AS borrow_count
    FROM borrow_requests br
    JOIN books b ON br.book_id = b.book_id
    GROUP BY b.bookname
    ORDER BY borrow_count DESC
    LIMIT 10
")->fetchAll();

// -------------------------------------------------------------
// 2. BOOK REQUESTS SUMMARY
// -------------------------------------------------------------
$requestSummary = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM book_requests
    GROUP BY status
")->fetchAll();

// -------------------------------------------------------------
// 3. FINE COLLECTION SUMMARY
// -------------------------------------------------------------
$fineSummary = $pdo->query("
    SELECT SUM(fine) AS total_fines_incurred, SUM(CASE WHEN status='returned' THEN fine ELSE 0 END) AS total_fines_collected
    FROM borrow_requests
")->fetch();

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin Reports</title>
<<<<<<< HEAD
<link rel="stylesheet" href="../css/style.css"> 
<link rel="stylesheet" href="../css/admin.css">
=======
<link rel="stylesheet" href="css/style.css"> 
<link rel="stylesheet" href="css/admin.css">
>>>>>>> 5e4c1969f453c75c17a0be1ac2e61abfbe4e71d9
<style>
/* CSS specific to printing/reporting */
@media print {
    .navbar, .action-center, .no-print { display: none; }
    body { background: white; color: black; margin: 0; padding: 0; }
    .admin-content { box-shadow: none; border: none; background: white; }
    h2, h3 { border-bottom: 1px solid #ccc !important; color: #333 !important; }
    .report-section { margin-bottom: 30px; }
}
</style>
</head><body>
<?php include '../navbar.php'; ?>

<div class="admin-grid">
<div class="admin-content" style="max-width: 900px; margin: 40px auto; background: white; color: #2d0115;">
    <h2 style="color: #2d0115; border-bottom: 3px solid #AE8625; padding-bottom: 5px;">Management Reports & Statistics</h2>
    <p class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn" style="background-color: #0056b3; color: white;">
            <i class="fas fa-print"></i> Print Report
        </button>
    </p>

    <!-- REPORT 1: BOOK REQUESTS -->
    <div class="report-section">
        <h3 style="color: #2d0115; border-bottom: 2px solid #f0f0f0;">Book Request Summary</h3>
        <table class="simple-table">
            <thead>
                <tr><th>Status</th><th>Count</th></tr>
            </thead>
            <tbody>
                <?php $totalRequests = 0; ?>
                <?php foreach($requestSummary as $r): $totalRequests += $r['count']; ?>
                    <tr>
                        <td><?php echo ucfirst(htmlspecialchars($r['status'])); ?></td>
                        <td><?php echo (int)$r['count']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background: #f7ef8a;">
                    <td>TOTAL REQUESTS</td>
                    <td><?php echo $totalRequests; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- REPORT 2: FINE COLLECTION -->
    <div class="report-section">
        <h3 style="color: #2d0115; border-bottom: 2px solid #f0f0f0;">Fine Collection Overview (RM)</h3>
        <table class="simple-table">
            <thead>
                <tr><th>Total Fines Incurred</th><th>Total Fines Paid/Collected</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color: red; font-weight: bold;">RM <?php echo number_format($fineSummary['total_fines_incurred'] ?: 0, 2); ?></td>
                    <td style="color: green; font-weight: bold;">RM <?php echo number_format($fineSummary['total_fines_collected'] ?: 0, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- REPORT 3: TOP 10 BORROWED BOOKS -->
    <div class="report-section">
        <h3 style="color: #2d0115; border-bottom: 2px solid #f0f0f0;">Top 10 Most Borrowed Books</h3>
        <table class="simple-table">
            <thead>
                <tr><th>Rank</th><th>Book Title</th><th>Times Borrowed</th></tr>
            </thead>
            <tbody>
                <?php $rank = 1; ?>
                <?php foreach($topBorrowed as $b): ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo htmlspecialchars($b['bookname']); ?></td>
                        <td><?php echo (int)$b['borrow_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($topBorrowed)): ?>
                    <tr><td colspan="3" style="text-align: center;">No borrowing activity recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</div>
</body></html>
