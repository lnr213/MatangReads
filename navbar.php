<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_profile_pic = null;

// Fetch profile picture if the user is logged in
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
        if ($user_data && $user_data['profile_pic']) {
            $user_profile_pic = "Images/profiles/" . htmlspecialchars($user_data['profile_pic']);
        }
    } catch (Exception $e) {
        // Log error but continue execution without profile pic
    }
}

// Fallback image path if no custom image is set
$default_profile_pic = "Images/profiles/default_user.png";
?>
<link rel="stylesheet" href="css/navbar.css">
<nav class="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo">📚 MatangReads</a>
    </div>
    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                <!-- Admin punya -->
                <a href="admin/admin_home.php">Admin Home</a>
                <a href="admin/manage_books.php">Books</a>
                <a href="admin/manage_users.php">Users</a>
                <a href="admin/manage_reservations.php">Reservations</a>
                <a href="admin/manage_payments.php">Payments</a> 
                <a href="admin/reports.php">Reports</a>
            <?php else: ?>
                <!-- User -->
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="request_book.php">Request</a>
                <a href="invoice.php">Invoices</a>
                <a href="profile.php" class="profile-link-with-pic">
                    <img src="<?php echo $user_profile_pic ?: $default_profile_pic; ?>" alt="Profile Picture" class="nav-profile-pic">
                    Profile
                </a>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
