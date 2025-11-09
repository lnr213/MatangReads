<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int) $_GET['id'];

    if (in_array($action, ['approved', 'rejected'])) {
        // Update the status of the book request
        $stmt = $pdo->prepare("UPDATE book_requests SET status = ? WHERE req_id = ?");
        $stmt->execute([$action, $id]);

        // OPTIONAL: If approved, you could notify the user or add the book to the main catalog.
    }

    header('Location: manage_book_requests.php');
    exit;
}

// Fetch all book requests
$requests = $pdo->query("
    SELECT br.*, u.username, u.full_name
    FROM book_requests br
    LEFT JOIN users u ON br.user_id = u.user_id
    ORDER BY br.created_at DESC
")->fetchAll();

// Initialize the counter variable
$count = 0;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Book Requests</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="admin-grid">
    <div class="admin-content">
        <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">User Book Requests</h2>

        <table class="simple-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>User</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $r): $count++; ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo htmlspecialchars($r['full_name'] ?: $r['username']); ?></td>
                    <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($r['author']); ?></td>
                    <td><?php echo htmlspecialchars($r['category']); ?></td>
                    <td><?php echo htmlspecialchars($r['notes']); ?></td>
                    <td><span class="status-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                    <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <a href="manage_book_requests.php?action=approved&id=<?php echo $r['req_id']; ?>" style="color: green;">Approve</a> |
                            <a href="manage_book_requests.php?action=rejected&id=<?php echo $r['req_id']; ?>" style="color: red;">Reject</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($requests)): ?>
                <tr><td colspan="9" style="text-align:center;">No book requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
