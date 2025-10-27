<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

// delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent admin from deleting themselves (optional safety check)
    if ($id !== $_SESSION['user_id']) { 
        $pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$id]);
        $msg = "<p class='success'>User deleted successfully.</p>";
    } else {
        $msg = "<p class='error'>Error: You cannot delete your own active admin account.</p>";
    }
    header('Location: manage_users.php');
    exit;
}

// reset pw to 'password123' (example)
if (isset($_GET['reset'])) {
    $id = (int)$_GET['reset'];
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password=? WHERE user_id=?")->execute([$hash, $id]);
    $msg = "<p class='success'>User password reset to 'password123'.</p>";
    header('Location: manage_users.php');
    exit;
}

// Fetch all users
$users = $pdo->query("SELECT user_id, username, email, user_type, full_name, date_joined FROM users ORDER BY user_id DESC")->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Manage Users</title>
<!-- IMPORTANT: Import both style.css and admin.css -->
<link rel="stylesheet" href="/matangreads/css/style.css"> 
<link rel="stylesheet" href="/matangreads/css/admin.css">

</head><body>
<?php include '../navbar.php'; ?>

<!-- Wrapped content in Admin Grid/Content for proper styling -->
<div class="admin-grid">
<div class="admin-content" style="max-width: 900px; margin: 40px auto;">
  <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">Manage Users</h2>
  
  <!-- Display success/error messages after actions -->
  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'deleted') echo "<p class='success'>User deleted successfully.</p>"; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'reset') echo "<p class='success'>User password reset successfully.</p>"; ?>

  <table class="simple-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Type</th>
            <th>Joined</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?php echo $u['user_id'];?></td>
        <td><?php echo htmlspecialchars($u['username']);?></td>
        <td><?php echo htmlspecialchars($u['full_name']);?></td>
        <td><?php echo htmlspecialchars($u['email']);?></td>
        <td><?php echo htmlspecialchars($u['user_type']);?></td>
        <td><?php echo date('Y-m-d', strtotime($u['date_joined']));?></td>
        <td>
          <a onclick="return confirm('Reset password for <?php echo $u['username'];?>? (Set to: password123)')" href="manage_users.php?reset=<?php echo $u['user_id'];?>&msg=reset">Reset PW</a> |
          <a onclick="return confirm('Are you sure you want to delete <?php echo $u['username'];?>?')" href="manage_users.php?delete=<?php echo $u['user_id'];?>&msg=deleted">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</body></html>
