<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$uid = $_SESSION['user_id'];
$msg = '';

// --- Handle POST Request ---

// Check if the request is for password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $user = $pdo->prepare("SELECT password FROM users WHERE user_id=?");
    $user->execute([$uid]);
    $u = $user->fetch();

    if (!password_verify($current_password, $u['password'])) {
        $msg = "<p class='error'>Error: Current password is incorrect.</p>";
    } elseif ($new_password !== $confirm_password) {
        $msg = "<p class='error'>Error: New passwords do not match.</p>";
    } elseif (strlen($new_password) < 6) {
        $msg = "<p class='error'>Error: Password must be at least 6 characters long.</p>";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password=? WHERE user_id=?");
        $update->execute([$hash, $uid]);
        $msg = "<p class='success'>Password updated successfully!</p>";
    }
} 
// Check if the request is for profile update
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fullname = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $current_image = $_POST['current_profile_pic'] ?? '';
    $image = $current_image;
    
    // Handle image upload
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_pic']['tmp_name'];
        $image = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
        
        $upload_dir = __DIR__ . "/Images/profiles/";
        
        if (move_uploaded_file($tmp, $upload_dir . $image)) {
            // Success! Delete old file if it wasn't the default placeholder
            if ($current_image && $current_image !== 'placeholder.png') {
                 @unlink($upload_dir . $current_image);
            }
        } else {
            $msg = "<p class='error'>Error uploading profile picture. Check folder permissions.</p>";
            // Revert image name to current one if upload fails
            $image = $current_image; 
        }
    }
    
    // Update personal details and the image path
    if (empty($msg)) {
        $update = $pdo->prepare("UPDATE users SET full_name=?, email=?, tel_no=?, profile_pic=? WHERE user_id=?");
        $update->execute([$fullname, $email, $tel, $image, $uid]);
        $msg = "<p class='success'>Personal details and picture updated successfully!</p>";
    }
}

// --- Fetch User Data for Display ---
$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$uid]);
$user = $user->fetch();

$profile_pic_filename = $user['profile_pic'] ?? '';
$profile_pic_src = $profile_pic_filename 
    ? '/matangreads/Images/profiles/' . htmlspecialchars($profile_pic_filename)
    : 'https://placehold.co/120x120/5f042f/ffffff?text=User'; // Default placeholder
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Profile Settings - MatangReads</title>
<link rel="stylesheet" href="/matangreads/css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
<div class="form-container">
  <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">Profile Settings</h2>
  <?php echo $msg; ?>

  <!-- -------------------- PROFILE DETAILS & PICTURE -------------------- -->
  <h3 style="margin-top: 20px;">Personal Details</h3>
  <form method="post" enctype="multipart/form-data" style="padding-top: 15px;">
    <input type="hidden" name="action" value="update_profile">
    <input type="hidden" name="current_profile_pic" value="<?php echo htmlspecialchars($profile_pic_filename); ?>">

    <div style="display: flex; align-items: center; margin-bottom: 20px;">
        <!-- APPLIED THE NEW CSS CLASS HERE -->
        <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" class="profile-pic-preview">
        
        <div style="flex-grow: 1;">
            <label for="profile_pic">Change Profile Picture</label>
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="width: auto;">
        </div>
    </div>

    <label>Full name</label><input name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? '')?>">
    <label>Email</label><input name="email" type="email" value="<?php echo htmlspecialchars($user['email'] ?? '')?>">
    <label>Phone</label><input name="tel" value="<?php echo htmlspecialchars($user['tel_no'] ?? '')?>">

    <div class="form-buttons"><button type="submit" class="btn">Save Details & Picture</button></div>
  </form>

  <!-- -------------------- CHANGE PASSWORD -------------------- -->
  <h3 style="margin-top: 40px; border-top: 1px dashed #ccc; padding-top: 20px;">Change Password</h3>
  <form method="post">
    <input type="hidden" name="action" value="update_password">

    <label>Current Password</label><input name="current_password" type="password" required>
    <label>New Password</label><input name="new_password" type="password" required>
    <label>Confirm Password</label><input name="confirm_password" type="password" required>

    <div class="form-buttons"><button type="submit" class="btn">Update Password</button></div>
  </form>

</div>
</div>
</body>
</html>
