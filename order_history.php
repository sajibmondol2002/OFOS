<?php
require_once __DIR__ . '/inc/functions.php';
requireLogin();

$userId = $_SESSION['user']['id'];
$orders = db_fetch_all('SELECT id, total_amount, status, delivery_address, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC', 'i', [$userId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Online Food Ordering</title>
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
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>My Orders</h2>
    </section>

    <?php if (empty($orders)): ?>
        <div class="card"><div class="card-body"><p>You have not placed any orders yet.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo sanitize($order['id']); ?></td>
                        <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                        <td><span class="status-pill <?php echo sanitize(str_replace(' ', '-', strtolower($order['status']))); ?>"><?php echo sanitize($order['status']); ?></span></td>
                        <td><?php echo sanitize($order['created_at']); ?></td>
                        <td><a class="button button-primary" href="order_success.php?id=<?php echo sanitize($order['id']); ?>">Track</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
