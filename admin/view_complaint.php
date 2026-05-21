<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) redirect('complaints.php');

$c = db_fetch_one('SELECT c.*, u.name AS customer, u.email AS customer_email FROM complaints c JOIN users u ON u.id = c.user_id WHERE c.id = ?', 'i', [$id]);
if (!$c) redirect('complaints.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'resolve') {
        $note = trim($_POST['admin_note'] ?? '');
        db_execute('UPDATE complaints SET status = ?, admin_note = ?, resolved_by = ?, resolved_at = ? WHERE id = ?', 'ssisi', ['resolved', $note, $_SESSION['user']['id'], date('Y-m-d H:i:s'), $id]);
        redirect('complaints.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - Complaint</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="complaints.php">Complaints</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Complaint #<?php echo sanitize($c['id']); ?></h2>
    </section>
    <div class="card"><div class="card-body">
        <p><strong>Customer:</strong> <?php echo sanitize($c['customer']); ?> (<?php echo sanitize($c['customer_email']); ?>)</p>
        <p><strong>Order ID:</strong> <?php echo sanitize($c['order_id'] ?? 'N/A'); ?></p>
        <p><strong>Subject:</strong> <?php echo sanitize($c['subject']); ?></p>
        <p><strong>Message:</strong><br><?php echo nl2br(sanitize($c['message'])); ?></p>
        <p><strong>Status:</strong> <?php echo sanitize($c['status']); ?></p>
        <?php if (!empty($c['admin_note'])): ?>
            <p><strong>Admin note:</strong><br><?php echo nl2br(sanitize($c['admin_note'])); ?></p>
        <?php endif; ?>

        <?php if ($c['status'] !== 'resolved'): ?>
            <form method="post" action="view_complaint.php?id=<?php echo sanitize($c['id']); ?>">
                <div class="input-group">
                    <label>Admin note (optional)</label>
                    <textarea name="admin_note" rows="4" style="width:100%;"></textarea>
                </div>
                <input type="hidden" name="action" value="resolve">
                <button class="button button-primary" type="submit">Mark Resolved</button>
                <a href="complaints.php" class="button">Back</a>
            </form>
        <?php else: ?>
            <p><a href="complaints.php" class="button">Back</a></p>
        <?php endif; ?>
    </div></div>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
