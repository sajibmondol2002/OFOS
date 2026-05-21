<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$message = '';
$error = '';

$categories = db_fetch_all('SELECT MIN(id) AS id, name FROM categories GROUP BY name ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $image = 'placeholder.png';

    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = mime_content_type($_FILES['image_file']['tmp_name']);
        if (in_array($mime, $allowed)) {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/images/restaurants/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image = 'restaurants/' . $filename;
            } else {
                $error = 'Image upload failed. Check folder permissions.';
            }
        } else {
            $error = 'Only JPG, PNG, WEBP, GIF images are allowed.';
        }
    }

    if (empty($error)) {
        if ($name === '' || $price <= 0 || $categoryId <= 0) {
            $error = 'Name, category and price are required.';
        } else {
            db_execute('INSERT INTO products (category_id, name, description, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)', 'issdsss', [$categoryId, $name, $description, $price, $image, $status, date('Y-m-d H:i:s')]);
            $message = 'Product saved successfully.';
        }
    }
}

$products = db_fetch_all('SELECT p.id, p.name, p.price, p.status, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products - Online Food Ordering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin Products</div>
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
        <h2>Manage Products</h2>
    </section>
    <?php if ($message): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo sanitize($error); ?></div><?php endif; ?>
    <div class="grid grid-2">
        <div class="form-card">
            <h3>Add Menu Item</h3>
            <form method="post" action="products.php" enctype="multipart/form-data">
                <div class="input-group">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" type="text" name="name">
                </div>
                <div class="textarea-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <div class="input-group">
                    <label class="form-label" for="price">Price</label>
                    <input id="price" type="number" name="price" step="0.01" min="0.01">
                </div>
                <div class="input-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo sanitize($category['id']); ?>"><?php echo sanitize($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label class="form-label" for="image_file">Product Image</label>
                    <input id="image_file" type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small style="color:#888;">JPG, PNG, WEBP or GIF. Leave empty to use placeholder.</small>
                </div>
                <div class="input-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="button button-primary">Create Product</button>
            </form>
        </div>

        <div>
            <h3>Menu Items</h3>
            <?php if (empty($products)): ?>
                <div class="card"><div class="card-body"><p>No products found.</p></div></div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Name</th><th>Category</th><th>Price</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo sanitize($product['name']); ?></td>
                                <td><?php echo sanitize($product['category_name']); ?></td>
                                <td><?php echo formatCurrency((float)$product['price']); ?></td>
                                <td><?php echo sanitize($product['status']); ?></td>
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