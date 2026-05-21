<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

// Handle POST actions: update status or reject (delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restaurant_id']) && isset($_POST['status'])) {
        $rid = (int) $_POST['restaurant_id'];
        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
        db_execute('UPDATE users SET status = ?, is_active = ? WHERE id = ?', 'sii', [$status, $status === 'active' ? 1 : 0, $rid]);
        db_execute('UPDATE restaurants SET is_approved = ? WHERE manager_id = ?', 'ii', [$status === 'active' ? 1 : 0, $rid]);
    }
    if (isset($_POST['reject_id'])) {
        $rid = (int) $_POST['reject_id'];
        db_execute('DELETE FROM users WHERE id = ?', 'i', [$rid]);
    }
    // redirect to avoid resubmission
    redirect('restaurants.php');
}

$restaurants = db_fetch_all(
    "SELECT u.id, u.name AS manager_name, u.email, u.status, u.created_at,
            r.name AS restaurant_name, r.cuisine_type, r.address, r.city, r.is_open, r.is_approved
     FROM users u
     LEFT JOIN restaurants r ON r.manager_id = u.id
     WHERE u.role = 'restaurant_manager'
     ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Restaurants</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
        <div class="container">
        <div class="site-title">Admin - Restaurants</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Orders</a>
            <a href="restaurants.php">Restaurants</a>
            <a href="customers.php">Customers</a>
            <a href="delivery_agents.php">Delivery Agents</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Restaurant Accounts</h2>
    </section>

    <?php if (empty($restaurants)): ?>
        <div class="card"><div class="card-body"><p>No restaurant accounts found.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $r): ?>
                    <tr>
                        <td><?php echo sanitize($r['restaurant_name'] ?? 'Profile not submitted'); ?><br><small><?php echo sanitize($r['manager_name']); ?></small></td>
                        <td><?php echo sanitize($r['email']); ?></td>
                        <td><?php echo sanitize($r['status']); ?> / <?php echo (int)($r['is_approved'] ?? 0) === 1 ? 'approved' : 'pending'; ?></td>
                        <td><?php echo sanitize($r['created_at']); ?></td>
                        <td>
                            <form method="post" action="restaurants.php" style="display:inline-flex; gap:8px; align-items:center;">
                                <input type="hidden" name="restaurant_id" value="<?php echo sanitize($r['id']); ?>">
                                <select name="status">
                                    <option value="active" <?php echo $r['status'] === 'active' ? 'selected' : ''; ?>>Approve / Active</option>
                                    <option value="inactive" <?php echo $r['status'] === 'inactive' ? 'selected' : ''; ?>>Reject / Inactive</option>
                                </select>
                                <button type="submit" class="button">Save</button>
                            </form>
                            <form method="post" action="restaurants.php" style="display:inline; margin-left:8px;">
                                <input type="hidden" name="reject_id" value="<?php echo sanitize($r['id']); ?>">
                                <button type="submit" class="button button-danger" onclick="return confirm('Reject and delete this registration? This will remove all related data.')">Reject</button>
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
