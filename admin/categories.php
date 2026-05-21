<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        db_execute('INSERT INTO categories (name, description, created_at) VALUES (?, ?, ?)', 'sss', [$name, $description, date('Y-m-d H:i:s')]);
        $message = 'Category created successfully.';
    }
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    if ($deleteId > 0) {
        db_execute('DELETE FROM categories WHERE id = ?', 'i', [$deleteId]);
        redirect('categories.php');
    }
}

$categories = db_fetch_all('SELECT id, name, description, created_at FROM categories ORDER BY name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Categories - Online Food Ordering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin Categories</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="products.php">Products</a>
            <a href="categories.php">Categories</a>
            <a href="../index.php">Shop</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Manage Categories</h2>
    </section>
    <?php if ($message): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo sanitize($error); ?></div><?php endif; ?>
    <div class="grid grid-2">
        <div class="form-card">
            <h3>Add Category</h3>
            <form method="post" action="categories.php">
                <div class="input-group">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" type="text" name="name" >
                </div>
                <div class="textarea-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <button type="submit" class="button button-primary">Save Category</button>
            </form>
        </div>

        <div>
            <h3>Existing Categories</h3>
            <?php if (empty($categories)): ?>
                <div class="card"><div class="card-body"><p>No categories found.</p></div></div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Name</th><th>Description</th><th>Created</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo sanitize($category['name']); ?></td>
                                <td><?php echo sanitize($category['description']); ?></td>
                                <td><?php echo sanitize($category['created_at']); ?></td>
                                <td><a href="categories.php?delete=<?php echo sanitize($category['id']); ?>" onclick="return confirm('Delete this category?');">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>
<footer>
    <div class="container"><p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p></div>
</footer>
</body>
</html>
