<?php
require_once 'config.php';
session_start();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        
       
        if ($user['user_type'] === 'admin') {
            header('Location: admin/admin_home.php'); // Admin goes to Admin Home
        } else {
            header('Location: index.php'); // User goes to User Dashboard
        }
        // -------------------------------
        
        exit;
    } else {
        $err = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login - MatangReads</title>
<link rel="stylesheet" href="css/login.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<div class="login-container">
  <h2>Login</h2>
  <?php if($err) echo "<p class='error'>".htmlspecialchars($err)."</p>"; ?>
  <?php if(isset($_GET['registered'])) echo "<p class='success'>Registration successful. Please login.</p>"; ?>
  <form method="post">
    <label>Username</label><input name="username" required>
    <label>Password</label><input name="password" type="password" required>
    <div class="form-buttons"><button type="submit">Login</button></div>
    <p style="text-align: center; margin-top: 15px;"><a href="register.php" style="color: white; text-decoration: none;">Don't have an account? Register here.</a></p>
  </form>
</div>
</main>
</body>
</html>
