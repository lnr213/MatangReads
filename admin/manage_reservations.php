<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'returned') {
        $return_date = date('Y-m-d');
        
        // 1. Get reservation details to calculate fine
        $stmt_check = $pdo->prepare("SELECT book_id, due_date FROM borrow_requests WHERE borrow_id = ?");
        $stmt_check->execute([$id]);
        $request = $stmt_check->fetch();
        
        if ($request) {
            // USES calculate_fine() FROM config.php
            $fine = calculate_fine($request['due_date'], $return_date);
            
            // 2. Update the reservation, fine, and return date
            $pdo->prepare("UPDATE borrow_requests SET status = 'returned', return_date = ?, fine = ? WHERE borrow_id = ?")
                ->execute([$return_date, $fine, $id]);
            
            // 3. Return book to stock
            $pdo->prepare("UPDATE books SET quantity = quantity + 1 WHERE book_id = ?")
                ->execute([$request['book_id']]);
        }
    } elseif (in_array($action, ['approved','rejected'])) {
        $pdo->prepare("UPDATE borrow_requests SET status = ? WHERE borrow_id = ?")->execute([$action, $id]);
    }

    header('Location: manage_reservations.php');
    exit;
}

// -------------------------------------------------------------
// 3. FETCH DATA FOR DISPLAY
// -------------------------------------------------------------
$rows = $pdo->query("SELECT br.*, b.bookname, u.username 
    FROM borrow_requests br 
    JOIN books b ON br.book_id=b.book_id 
    JOIN users u ON br.user_id=u.user_id 
    ORDER BY br.created_at DESC")->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Manage Reservations</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
</head><body>
<?php include '../navbar.php'; ?>
<div class="admin-grid">
<div class="admin-content">
  <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">Reservations & Fines</h2>
  <table class="simple-table">
    <thead><tr><th>ID</th><th>User</th><th>Book</th><th>Borrow</th><th>Due</th><th>Status</th><th>Fine (RM)</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): 
        // Calculate the fine for display
        $fine_display = $r['fine'];
        // USES calculate_fine() FROM config.php
        if ($r['status'] === 'approved' && $r['due_date'] < date('Y-m-d')) {
            $fine_display = calculate_fine($r['due_date']);
        }
      ?>
        <tr>
          <td><?php echo $r['borrow_id'];?></td>
          <td><?php echo htmlspecialchars($r['username']);?></td>
          <td><?php echo htmlspecialchars($r['bookname']);?></td>
          <td><?php echo $r['borrow_date'];?></td>
          <td><?php echo $r['due_date'];?></td>
          <td><span class="status-<?php echo $r['status']; ?>"><?php echo $r['status'];?></span></td>
          <td style="color:<?php echo $fine_display > 0 ? 'red' : 'green'; ?>;">
            <?php echo number_format($fine_display, 2); ?>
          </td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
            <a href="manage_reservations.php?action=approved&id=<?php echo $r['borrow_id'];?>">Approve</a> |
            <a href="manage_reservations.php?action=rejected&id=<?php echo $r['borrow_id'];?>">Reject</a>
            <?php elseif ($r['status'] === 'approved'): ?>
            <a href="manage_reservations.php?action=returned&id=<?php echo $r['borrow_id'];?>">Mark Returned</a>
            <?php else: ?>
            -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
</div>
</body></html>
