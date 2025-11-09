<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['bookname'];
    $author = $_POST['author'];
    $cat = $_POST['category'];
    $desc = $_POST['description'];
    $quantity = (int) $_POST['quantity'];

    $imgName = '';
    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        $imgName = basename($_FILES['image']['name']);
        @move_uploaded_file($tmp, __DIR__ . "/../Images/" . $imgName);
    }

    if (!$name || $quantity < 1) {
        $msg = "<p class='error'>Error: Title and Quantity are required.</p>";
    } else {
        $ins = $pdo->prepare("INSERT INTO books (bookname, author, category, description, image, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$name, $author, $cat, $desc, $imgName, $quantity]);
        $msg = "<p class='success'>Book '{$name}' added successfully.</p>";
    }
}

// Delete book
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM books WHERE book_id = ?")->execute([$id]);
    $msg = "<p class='success'>Book deleted successfully.</p>";
    header('Location: manage_books.php');
    exit;
}

// Search books or authors
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (bookname LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY book_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

// Initialize counter
$count = 0;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Books</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="admin-grid">
    <div class="admin-content">
        <h2 style="color: #2d0115; border-bottom: 2px solid #AE8625; padding-bottom: 5px;">Manage Books</h2>
        <?php echo $msg; ?>

        <div class="form-container" style="background: #f7f7f7; border-top: none;">
            <h3 style="color: #2d0115;">Add New Book</h3>
            <form method="post" enctype="multipart/form-data" class="add-book-form">
                <input type="hidden" name="action" value="add">

                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <div style="flex: 1 1 30%;">
                        <label>Title</label>
                        <input name="bookname" required>
                    </div>
                    <div style="flex: 1 1 30%;">
                        <label>Author</label>
                        <input name="author">
                    </div>
                    <div style="flex: 1 1 30%;">
                        <label>Category</label>
                        <input name="category">
                    </div>
                    <div style="flex: 1 1 15%;">
                        <label>Quantity</label>
                        <input name="quantity" type="number" value="1" min="1" required>
                    </div>
                    <div style="flex: 1 1 40%;">
                        <label>Cover Image</label>
                        <input type="file" name="image">
                    </div>
                    <div style="flex: 1 1 100%;">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn">Add Book</button>
                </div>
            </form>
        </div>

        <h3 style="color: #2d0115; margin-top: 30px;">All Books</h3>

        <form method="get" class="search-inline" style="margin-bottom: 15px;">
            <input name="search" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>" style="width: 50%;">
            <button type="submit" class="btn" style="background-color: #AE8625; color: #2d0115;">Search</button>
        </form>

        <table class="simple-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Qty</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): $count++; ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo htmlspecialchars($b['bookname']); ?></td>
                    <td><?php echo htmlspecialchars($b['author']); ?></td>
                    <td><?php echo (int) $b['quantity']; ?></td>
                    <td>
                        <a href="edit_book.php?id=<?php echo $b['book_id']; ?>">Edit</a> |
                        <a onclick="return confirm('Are you sure you want to delete this book? This cannot be undone.');" href="manage_books.php?delete=<?php echo $b['book_id']; ?>">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($books)): ?>
                <tr><td colspan="5" style="text-align:center;">No books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
