<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $users = db_fetch_all('SELECT id, name, email, status, created_at FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC', 'sss', ['customer', $like, $like]);
} else {
    $users = db_fetch_all("SELECT id, name, email, status, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['status'])) {
        $uid = (int) $_POST['user_id'];
        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
        db_execute('UPDATE users SET status = ? WHERE id = ?', 'si', [$status, $uid]);
    }
    redirect('customers.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Customers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - Customers</div>
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
        <h2>Customer Accounts</h2>
    </section>

    <form method="get" action="customers.php" style="margin-bottom:12px;">
        <input type="search" name="q" placeholder="Search name or email" value="<?php echo sanitize($q); ?>">
        <button class="button">Search</button>
        <?php if ($q !== ''): ?> <a href="customers.php" class="button">Clear</a><?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
        <div class="card"><div class="card-body"><p>No customers found.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo sanitize($u['name']); ?></td>
                        <td><?php echo sanitize($u['email']); ?></td>
                        <td><?php echo sanitize($u['status']); ?></td>
                        <td><?php echo sanitize($u['created_at']); ?></td>
                        <td>
                            <a href="view_user.php?id=<?php echo sanitize($u['id']); ?>" class="button">View</a>
                            <form method="post" action="customers.php" style="display:inline-flex; gap:8px; align-items:center; margin-left:8px;">
                                <input type="hidden" name="user_id" value="<?php echo sanitize($u['id']); ?>">
                                <select name="status">
                                    <option value="active" <?php echo $u['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $u['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <button type="submit" class="button">Update</button>
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
