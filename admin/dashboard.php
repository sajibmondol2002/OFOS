<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$totalProducts = db_fetch_one('SELECT COUNT(*) AS count FROM products')['count'] ?? 0;
$totalCategories = db_fetch_one('SELECT COUNT(*) AS count FROM categories')['count'] ?? 0;
$totalOrders = db_fetch_one('SELECT COUNT(*) AS count FROM orders')['count'] ?? 0;
$recentOrders = db_fetch_all('SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 8');

// Platform admin metrics
$totalRegisteredUsers = db_fetch_one('SELECT COUNT(*) AS count FROM users')['count'] ?? 0;
$totalActiveRestaurants = db_fetch_one("SELECT COUNT(*) AS count FROM users WHERE role = 'restaurant_manager' AND status = 'active'")['count'] ?? 0;
$totalOrdersToday = db_fetch_one("SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$totalActiveDeliveryAgents = db_fetch_one("SELECT COUNT(*) AS count FROM users WHERE role = 'delivery_man' AND COALESCE(is_available,0) = 1")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Food Ordering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin Dashboard</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="featured_restaurants.php">Featured</a>
            <a href="customers.php">Customers</a>
            <a href="delivery_agents.php">Delivery Agents</a>
            <a href="complaints.php">Complaints</a>
            <a href="settings.php">Settings</a>
            <a href="../index.php">Shop</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Welcome, <?php echo sanitize($_SESSION['user']['name']); ?></h2>
    </section>

    <div class="grid grid-2" style="margin-bottom:24px;">
        <div class="card">
            <div class="card-body">
                <h3>Active Restaurants</h3>
                <p><?php echo sanitize($totalActiveRestaurants); ?> active restaurants</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Orders Today</h3>
                <p><?php echo sanitize($totalOrdersToday); ?> orders placed today</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Registered Users</h3>
                <p><?php echo sanitize($totalRegisteredUsers); ?> total registered users</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Active Delivery Agents</h3>
                <p><?php echo sanitize($totalActiveDeliveryAgents); ?> available agents</p>
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-bottom:24px;">
        <div class="card">
            <div class="card-body">
                <h3>Products</h3>
                <p><?php echo sanitize($totalProducts); ?> active menu items</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Categories</h3>
                <p><?php echo sanitize($totalCategories); ?> categories available</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Orders</h3>
                <p><?php echo sanitize($totalOrders); ?> total orders</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Quick Links</h3>
                <p><a href="orders.php">Review orders</a></p>
            </div>
        </div>
    </div>

    <section class="section-title">
        <h2>Recent Orders</h2>
    </section>

    <?php if (empty($recentOrders)): ?>
        <div class="card"><div class="card-body"><p>No recent orders found.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><?php echo sanitize($order['id']); ?></td>
                        <td><?php echo sanitize($order['customer']); ?></td>
                        <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                        <td><?php echo sanitize($order['status']); ?></td>
                        <td><?php echo sanitize($order['created_at']); ?></td>
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
