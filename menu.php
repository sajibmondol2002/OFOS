<?php
require_once __DIR__ . '/inc/functions.php';
requireLogin();

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$categories = db_fetch_all('SELECT MIN(id) AS id, name FROM categories GROUP BY name ORDER BY name');

if ($categoryId > 0) {
    $selectedCategory = db_fetch_one('SELECT name FROM categories WHERE id = ?', 'i', [$categoryId]);
    $selectedName = $selectedCategory['name'] ?? null;
}

if (!empty($selectedName)) {
    $products = db_fetch_all(
        'SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE c.name = ? AND p.status = ?
         ORDER BY p.name',
        'ss',
        [$selectedName, 'active']
    );
} else {
    $products = db_fetch_all(
        'SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.status = ? ORDER BY p.name',
        's',
        ['active']
    );
}
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Online Food Ordering</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Online Food Ordering</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart (<?php echo cartCount(); ?>)</a>
            <?php if ($currentUser): ?>
                <a href="order_history.php">My Orders</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Menu</h2>
        <form method="get" action="menu.php">
            <select name="category" onchange="this.form.submit()">
                <option value="0">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo sanitize($category['id']); ?>" <?php echo $category['id'] === $categoryId ? 'selected' : ''; ?>><?php echo sanitize($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <div class="grid grid-3">
        <?php if (empty($products)): ?>
            <div class="card"><div class="card-body"><p>No products found.</p></div></div>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <div class="card">
                <img src="assets/images/<?php echo sanitize($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x300?text=Food';">
                <div class="card-body">
                    <h3><?php echo sanitize($product['name']); ?></h3>
                    <p><?php echo sanitize($product['category_name']); ?></p>
                    <p><?php echo sanitize($product['description']); ?></p>
                    <p><strong><?php echo formatCurrency((float)$product['price']); ?></strong></p>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo sanitize($product['id']); ?>">
                        <button type="submit" class="button button-primary">Add to Cart</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
