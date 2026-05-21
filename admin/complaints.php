<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

// Handle resolve action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['complaint_id']) && isset($_POST['action'])) {
        $cid = (int) $_POST['complaint_id'];
        $action = $_POST['action'];
        if ($action === 'resolve') {
            $note = trim($_POST['admin_note'] ?? '');
            db_execute('UPDATE complaints SET status = ?, admin_note = ?, resolved_by = ?, resolved_at = ? WHERE id = ?', 'ssisi', ['resolved', $note, $_SESSION['user']['id'], date('Y-m-d H:i:s'), $cid]);
        }
    }
    redirect('complaints.php');
}

$complaints = db_fetch_all('SELECT c.id, c.order_id, c.subject, c.status, c.created_at, u.name AS customer FROM complaints c JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Complaints</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - Complaints</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="customers.php">Customers</a>
            <a href="delivery_agents.php">Delivery Agents</a>
            <a href="complaints.php">Complaints</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Customer Complaints</h2>
    </section>

    <?php if (empty($complaints)): ?>
        <div class="card"><div class="card-body"><p>No complaints found.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>ID</th><th>Customer</th><th>Subject</th><th>Status</th><th>Placed</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?php echo sanitize($c['id']); ?></td>
                        <td><?php echo sanitize($c['customer']); ?></td>
                        <td><?php echo sanitize($c['subject']); ?></td>
                        <td><?php echo sanitize($c['status']); ?></td>
                        <td><?php echo sanitize($c['created_at']); ?></td>
                        <td>
                            <a href="view_complaint.php?id=<?php echo sanitize($c['id']); ?>" class="button">View</a>
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
