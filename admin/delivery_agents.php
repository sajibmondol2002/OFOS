<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $agents = db_fetch_all('SELECT id, name, email, status, is_available, created_at FROM users WHERE role = ? AND (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC', 'sss', ['delivery_man', $like, $like]);
} else {
    $agents = db_fetch_all("SELECT id, name, email, status, is_available, created_at FROM users WHERE role = 'delivery_man' ORDER BY created_at DESC");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agent_id'])) {
        $aid = (int) $_POST['agent_id'];
        if (isset($_POST['status'])) {
            $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
            db_execute('UPDATE users SET status = ? WHERE id = ?', 'si', [$status, $aid]);
        }
        if (isset($_POST['is_available'])) {
            $avail = $_POST['is_available'] === '1' ? 1 : 0;
            db_execute('UPDATE users SET is_available = ? WHERE id = ?', 'ii', [$avail, $aid]);
        }
    }
    if (isset($_POST['reject_id'])) {
        $rid = (int) $_POST['reject_id'];
        db_execute('DELETE FROM users WHERE id = ?', 'i', [$rid]);
    }
    redirect('delivery_agents.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Delivery Agents</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - Delivery Agents</div>
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
        <h2>Delivery Agent Accounts</h2>
    </section>

    <form method="get" action="delivery_agents.php" style="margin-bottom:12px;">
        <input type="search" name="q" placeholder="Search name or email" value="<?php echo sanitize($q); ?>">
        <button class="button">Search</button>
        <?php if ($q !== ''): ?> <a href="delivery_agents.php" class="button">Clear</a><?php endif; ?>
    </form>

    <?php if (empty($agents)): ?>
        <div class="card"><div class="card-body"><p>No delivery agents found.</p></div></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Status</th><th>Available</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $a): ?>
                    <tr>
                        <td><?php echo sanitize($a['name']); ?></td>
                        <td><?php echo sanitize($a['email']); ?></td>
                        <td><?php echo sanitize($a['status']); ?></td>
                        <td><?php echo sanitize($a['is_available']) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo sanitize($a['created_at']); ?></td>
                        <td>
                            <a href="view_user.php?id=<?php echo sanitize($a['id']); ?>" class="button">View</a>
                            <form method="post" action="delivery_agents.php" style="display:inline-flex; gap:8px; align-items:center; margin-left:8px;">
                                <input type="hidden" name="agent_id" value="<?php echo sanitize($a['id']); ?>">
                                <select name="status">
                                    <option value="active" <?php echo $a['status'] === 'active' ? 'selected' : ''; ?>>Approve / Active</option>
                                    <option value="inactive" <?php echo $a['status'] === 'inactive' ? 'selected' : ''; ?>>Reject / Inactive</option>
                                </select>
                                <select name="is_available">
                                    <option value="1" <?php echo (int)$a['is_available'] === 1 ? 'selected' : ''; ?>>Available</option>
                                    <option value="0" <?php echo (int)$a['is_available'] === 0 ? 'selected' : ''; ?>>Not available</option>
                                </select>
                                <button type="submit" class="button">Save</button>
                            </form>
                            <form method="post" action="delivery_agents.php" style="display:inline; margin-left:8px;">
                                <input type="hidden" name="reject_id" value="<?php echo sanitize($a['id']); ?>">
                                <button type="submit" class="button button-danger" onclick="return confirm('Reject and delete this agent? This will remove all related data.')">Reject</button>
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
