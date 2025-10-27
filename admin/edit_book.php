<?php
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php'); exit;
}
$id = (int)($_GET['id'] ?? 0);
$msg = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['bookname'];
    $author = $_POST['author'];
    $cat = $_POST['category'];
    $desc = $_POST['description'];
    $quantity = (int)$_POST['quantity'];
    $image_update = $_POST['current_image'];

    // Handle new image upload
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $image_update = basename($_FILES['image']['name']);
        // Attempt to move file
        if (@move_uploaded_file($tmp, __DIR__ . "/../Images/".$image_update)) {
            // Success
        } else {
            // File move failed, maybe permissions issue
            $msg = "<p class='error'>File upload failed. Check folder permissions.</p>";
            $image_update = $_POST['current_image']; // Revert to old image name
        }
    }

    if (!$msg) {
        // Prepare and execute the UPDATE statement
        $upd = $pdo->prepare("UPDATE books SET bookname=?, author=?, category=?, description=?, image=?, quantity=? WHERE book_id=?");
        $upd->execute([$name, $author, $cat, $desc, $image_update, $quantity, $id]);
        
        // Redirect back to manage_books.php after successful update (POST-Redirect-GET)
        header('Location: manage_books.php?msg=success'); // Added msg for confirmation
        exit;
    }
}

// Fetch the book data for display
$stmt = $pdo->prepare("SELECT * FROM books WHERE book_id=?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { header('Location: manage_books.php'); exit; }

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Edit Book - <?php echo htmlspecialchars($book['bookname']); ?></title>
<<<<<<< HEAD
<link rel="stylesheet" href="../css/style.css"> 
<link rel="stylesheet" href="../css/admin.css">
=======
<link rel="stylesheet" href="css/style.css"> 
<link rel="stylesheet" href="css/admin.css">
>>>>>>> 5e4c1969f453c75c17a0be1ac2e61abfbe4e71d9

</head><body>
<?php include '../navbar.php'; ?>

<!-- Wrapped content in the Admin Content Box -->
<div class="admin-grid">
<div class="admin-content" style="max-width: 800px; margin: 40px auto;">
  <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">Edit Book: <?php echo htmlspecialchars($book['bookname']); ?></h2>
  
  <?php if($msg) echo $msg; ?>
  <?php if($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['msg']) && $_GET['msg'] == 'success') echo "<p class='success'>Book updated successfully!</p>"; ?>

  <div class="add-form" style="background: #f7f7f7; padding: 25px; border-top: none;">
    <form method="post" enctype="multipart/form-data">
        
        <!-- Main container uses Flex for the two halves -->
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            
            <!-- LEFT COLUMN (uses Input Grid) -->
            <div style="flex: 1 1 calc(50% - 10px);">
                <div class="input-grid">
                    <label for="bookname">Title</label><input id="bookname" name="bookname" value="<?php echo htmlspecialchars($book['bookname']); ?>" required>
                </div>
                <div class="input-grid">
                    <label for="author">Author</label><input id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>">
                </div>
                <div class="input-grid">
                    <label for="category">Category</label><input id="category" name="category" value="<?php echo htmlspecialchars($book['category']); ?>">
                </div>
                <div class="input-grid">
                    <label for="quantity">Quantity</label><input id="quantity" name="quantity" type="number" value="<?php echo (int)$book['quantity']; ?>" min="1" required>
                </div>
            </div>

            <!-- RIGHT COLUMN (Description and Image) -->
            <div style="flex: 1 1 calc(50% - 10px);">
                <!-- Description Box -->
                <div style="margin-bottom: 15px;">
                    <label for="description" style="display: block; font-weight: bold; color: #333;">Description</label>
                    <textarea id="description" name="description" rows="7"><?php echo htmlspecialchars($book['description']); ?></textarea>
                </div>
                
                <!-- Image Section -->
                <label style="display: block; font-weight: bold; color: #333;">Current Image</label>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($book['image']); ?>">
                    <img src="Images/<?php echo htmlspecialchars($book['image']);?>" style="max-height: 80px; border: 1px solid #ddd; border-radius: 4px;">
                    <span style="color:#333; font-size: 0.9em;"><?php echo htmlspecialchars($book['image']); ?></span>
                </div>

                <label style="display: block; font-weight: bold; color: #333;">Change Cover Image</label>
                <input type="file" name="image" style="width: 100%;">
            </div>
        </div>
        
        <div class="form-buttons" style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; text-align: right;">
            <button type="submit" class="btn" style="background-color: #5f042f;">Update Book</button>
            <a href="manage_books.php" class="btn" style="background-color: #888;">Cancel</a>
        </div>
    </form>
  </div>
</div>
</div>
</body></html>
