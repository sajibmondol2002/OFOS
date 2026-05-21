<?php
require_once __DIR__ . '/inc/functions.php';
requireLogin();

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order = db_fetch_one('SELECT * FROM orders WHERE id = ? AND user_id = ?', 'ii', [$orderId, $_SESSION['user']['id']]);
if (!$order) {
    redirect('index.php');
}

$reviewMessage = '';
$reviewSubmitted = false;
$reviewExists = !empty(db_fetch_one('SELECT id FROM reviews WHERE order_id = ?', 'i', [$orderId]));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5 || $comment === '') {
        $reviewMessage = 'Please provide a valid rating and comment.';
    } elseif ($reviewExists) {
        $reviewMessage = 'You have already submitted a review for this order.';
    } else {
        db_execute(
            'INSERT INTO reviews (order_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?)',
            'iiiss',
            [$orderId, $_SESSION['user']['id'], $rating, $comment, date('Y-m-d H:i:s')]
        );
        $reviewSubmitted = true;
        $reviewExists = true;
        $reviewMessage = 'Thank you! Your review has been submitted.';
    }
}

$items = db_fetch_all('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?', 'i', [$orderId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Online Food Ordering</title>
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
            <a href="order_history.php">My Orders</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Order Details</h2>
    </section>
    <div class="alert alert-success">
        <strong>Current status:</strong> <span class="status-pill <?php echo sanitize(str_replace(' ', '-', strtolower($order['status']))); ?>"><?php echo sanitize($order['status']); ?></span>
    </div>
    <div class="card">
        <div class="card-body">
            <p><strong>Order ID:</strong> <?php echo sanitize($order['id']); ?></p>
            <p><strong>Status:</strong> <?php echo sanitize($order['status']); ?></p>
            <p><strong>Total:</strong> <?php echo formatCurrency((float)$order['total_amount']); ?></p>
            <p><strong>Delivery address:</strong> <?php echo sanitize($order['delivery_address']); ?></p>
            <p><strong>Placed at:</strong> <?php echo sanitize($order['created_at']); ?></p>
        </div>
    </div>

    <section class="section-title" style="margin-top:24px;">
        <h2>Order details</h2>
    </section>
    <table class="table">
        <thead>
            <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo sanitize($item['name']); ?></td>
                    <td><?php echo sanitize($item['quantity']); ?></td>
                    <td><?php echo formatCurrency((float)$item['price']); ?></td>
                    <td><?php echo formatCurrency((float)$item['subtotal']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <section class="section-title" style="margin-top:24px;">
        <h2>Leave a Review</h2>
    </section>

    <?php if ($reviewMessage): ?>
        <div class="alert <?php echo $reviewSubmitted ? 'alert-success' : 'alert-error'; ?>">
            <?php echo sanitize($reviewMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (!$reviewExists): ?>
        <div class="form-card" style="max-width:720px; margin-bottom:24px;">
            <form method="post" action="order_success.php?id=<?php echo sanitize($orderId); ?>">
                <div class="input-group">
                    <label class="form-label" for="rating">Rating</label>
                    <select id="rating" name="rating">
                        <option value="">Select rating</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="textarea-group">
                    <label class="form-label" for="comment">Comment</label>
                    <textarea id="comment" name="comment" rows="4" ><?php echo sanitize($_POST['comment'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="button button-primary">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-success">You have already submitted a review for this order.</div>
    <?php endif; ?>

    <a class="button button-primary" href="order_history.php">View My Orders</a>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
