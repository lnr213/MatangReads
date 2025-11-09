<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}

$msg = '';

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

// Initialize the counter variable
$count = 0;
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
  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'reset') echo "<p class='success'>User password reset successfully.</p>"; ?>

  <table class="simple-table">
    <thead>
        <tr>
            <th>No</th> 
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Type</th>
            <th>Joined</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): 
        $count++; // Increment the counter for each row
      ?>
      <tr>
        <td><?php echo $count;?></td> <!-- Display the counter -->
        <td><?php echo htmlspecialchars($u['username']);?></td>
        <td><?php echo htmlspecialchars($u['full_name']);?></td>
        <td><?php echo htmlspecialchars($u['email']);?></td>
        <td><?php echo htmlspecialchars($u['user_type']);?></td>
        <td><?php echo date('Y-m-d', strtotime($u['date_joined']));?></td>
        <td>
          <a onclick="return confirm('Reset password for <?php echo $u['username'];?>? (Set to: password123)')" href="manage_users.php?reset=<?php echo $u['user_id'];?>&msg=reset">Reset PW</a>
          <!-- DELETE LINK REMOVED -->
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</body></html>