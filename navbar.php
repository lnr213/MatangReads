<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_profile_pic = null;

// Fetch profile picture path if the user is logged in
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();
        if ($user_data && $user_data['profile_pic']) {
            $user_profile_pic = "/matangreads/Images/profiles/" . htmlspecialchars($user_data['profile_pic']);
        }
    } catch (Exception $e) {
        // Log error but continue execution without profile pic
    }
}

// Fallback image path if no custom image is set
$default_profile_pic = "/matangreads/Images/profiles/default_user.png";
?>
<link rel="stylesheet" href="/matangreads/css/navbar.css">
<nav class="navbar">
    <div class="nav-left">
        <a href="/matangreads/index.php" class="logo">📚 MatangReads</a>
    </div>
    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                <!-- Admin Links -->
                <a href="/matangreads/admin/admin_home.php">Admin Home</a>
                <a href="/matangreads/admin/manage_books.php">Books</a>
                <a href="/matangreads/admin/manage_users.php">Users</a>
                <a href="/matangreads/admin/manage_reservations.php">Reservations</a>
                <a href="/matangreads/admin/manage_payments.php">Payments</a> <!-- ADDED LINK HERE -->
                <a href="/matangreads/admin/reports.php">Reports</a>
            <?php else: ?>
                <!-- User Links -->
                <a href="/matangreads/dashboard.php">Dashboard</a>
                <a href="/matangreads/books.php">Books</a>
                <a href="/matangreads/request_book.php">Request</a>
                <a href="/matangreads/invoice.php">Invoices</a>
                <a href="/matangreads/profile.php" class="profile-link-with-pic">
                    <img src="<?php echo $user_profile_pic ?: $default_profile_pic; ?>" alt="Profile Picture" class="nav-profile-pic">
                    Profile
                </a>
            <?php endif; ?>
            <a href="/matangreads/logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <a href="/matangreads/login.php">Login</a>
            <a href="/matangreads/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
