<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    redirect('dashboard.php');
}

$user = db_fetch_one('SELECT id, name, email, phone, role, status, vehicle_type, profile_picture, is_available, created_at FROM users WHERE id = ?', 'i', [$id]);
if (!$user) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - View User</div>
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
        <h2>User Details</h2>
    </section>

    <div class="card">
        <div class="card-body">
            <p><strong>Name:</strong> <?php echo sanitize($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo sanitize($user['phone'] ?? ''); ?></p>
            <p><strong>Role:</strong> <?php echo sanitize($user['role']); ?></p>
            <p><strong>Status:</strong> <?php echo sanitize($user['status']); ?></p>
            <p><strong>Vehicle Type:</strong> <?php echo sanitize($user['vehicle_type'] ?? ''); ?></p>
            <p><strong>Available:</strong> <?php echo (int)$user['is_available'] === 1 ? 'Yes' : 'No'; ?></p>
            <p><strong>Registered:</strong> <?php echo sanitize($user['created_at']); ?></p>
            <?php if (!empty($user['profile_picture'])): ?>
                <p><strong>Profile:</strong><br><img src="../assets/images/profiles/<?php echo sanitize($user['profile_picture']); ?>" alt="profile" style="max-width:160px;"></p>
            <?php endif; ?>
            <p><a href="javascript:history.back()" class="button">Back</a></p>
        </div>
    </div>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
