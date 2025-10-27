<?php
require_once 'config.php';
session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $full = trim($_POST['full_name'] ?? '');

    if (!$username || !$password) $errors[] = 'Username and password required.';

    if (!$errors) {
        // check existing
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, password, email, tel_no, full_name) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$username, $hash, $email, $tel, $full]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register - MatangReads</title>
<link rel="stylesheet" href="css/login.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<div class="login-container">
  <h2>Register</h2>
  <?php if($errors) foreach($errors as $e) echo "<p class='error'>".htmlspecialchars($e)."</p>"; ?>
  <form method="post">
    <label>Full name</label><input name="full_name" value="<?php echo htmlspecialchars($full ?? '')?>">
    <label>Username *</label><input name="username" required value="<?php echo htmlspecialchars($username ?? '')?>">
    <label>Email</label><input name="email" value="<?php echo htmlspecialchars($email ?? '')?>">
    <label>Phone</label><input name="tel" value="<?php echo htmlspecialchars($tel ?? '')?>">
    <label>Password *</label><input name="password" type="password" required>
    <div class="form-buttons"><button type="submit">Create account</button></div>
  </form>
</div>
</main>
</body>
</html>
