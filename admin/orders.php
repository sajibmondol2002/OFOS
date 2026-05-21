<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

// Update order status via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = $_POST['status'] ?? '';
    $allowed = ['pending', 'preparing', 'delivered', 'cancelled'];
    if ($orderId > 0 && in_array($status, $allowed, true)) {
        db_execute('UPDATE orders SET status = ? WHERE id = ?', 'si', [$status, $orderId]);
    }
}

// Filters: status, date_from, date_to, customer (name/email), delivery_agent
$filters = [];
$types = '';
$params = [];

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '') {
    $filters[] = 'o.status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
if ($from !== '') {
    $filters[] = 'DATE(o.created_at) >= ?';
    $types .= 's';
    $params[] = $from;
}
if ($to !== '') {
    $filters[] = 'DATE(o.created_at) <= ?';
    $types .= 's';
    $params[] = $to;
}

$customer = trim($_GET['customer'] ?? '');
if ($customer !== '') {
    $filters[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $types .= 'ss';
    $params[] = "%$customer%";
    $params[] = "%$customer%";
}

$deliveryAgent = trim($_GET['delivery_agent'] ?? '');
if ($deliveryAgent !== '') {
    $filters[] = '(da.name LIKE ? OR da.email LIKE ?)';
    $types .= 'ss';
    $params[] = "%$deliveryAgent%";
    $params[] = "%$deliveryAgent%";
}

$where = '';
if (!empty($filters)) {
    $where = 'WHERE ' . implode(' AND ', $filters);
}

$sql = "SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS customer, da.name AS delivery_agent FROM orders o JOIN users u ON u.id = o.user_id LEFT JOIN users da ON da.id = o.delivery_agent_id $where ORDER BY o.created_at DESC";
if ($types !== '') {
    $orders = db_fetch_all($sql, $types, $params);
} else {
    $orders = db_fetch_all($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - Online Food Ordering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin Orders</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="../index.php">Shop</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Order Management</h2>
    </section>
    <?php if (empty($orders)): ?>
        <div class="card"><div class="card-body"><p>No orders available.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo sanitize($order['id']); ?></td>
                        <td><?php echo sanitize($order['customer']); ?></td>
                        <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                        <td><?php echo sanitize($order['status']); ?></td>
                        <td><?php echo sanitize($order['created_at']); ?></td>
                        <td>
                            <form method="post" action="orders.php" style="display:inline-flex; gap:8px; align-items:center;">
                                <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                                <select name="status">
                                    <?php foreach (['pending','preparing','delivered','cancelled'] as $statusOption): ?>
                                        <option value="<?php echo sanitize($statusOption); ?>" <?php echo $statusOption === $order['status'] ? 'selected' : ''; ?>><?php echo ucfirst($statusOption); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="button">Save</button>
                            </form>
                        </td>
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
