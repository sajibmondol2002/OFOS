<?php
require_once __DIR__ . '/inc/functions.php';
requireLogin();

$cartProducts = getCartProducts();
if (empty($cartProducts)) {
    redirect('cart.php');
}

$currentUser = getCurrentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deliveryAddress = sanitize($_POST['delivery_address'] ?? '');
    if ($deliveryAddress === '') {
        $error = 'Delivery address is required.';
    }

    if ($error === '') {
        $orderTotal = cartTotal();
        db_execute(
            'INSERT INTO orders (user_id, total_amount, delivery_address, status, created_at) VALUES (?, ?, ?, ?, ?)',
            'idsss',
            [$currentUser['id'], $orderTotal, $deliveryAddress, 'pending', date('Y-m-d H:i:s')]
        );
        $orderId = db_insert_id();

        foreach ($cartProducts as $product) {
            db_execute(
                'INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)',
                'iiidd',
                [$orderId, $product['id'], $product['quantity'], $product['price'], $product['subtotal']]
            );
        }

        clearCart();
        redirect('order_success.php?id=' . $orderId);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Food Ordering</title>
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
        <h2>Checkout</h2>
    </section>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="grid grid-2">
        <div class="form-card">
            <h3>Delivery details</h3>
            <form method="post" action="checkout.php">
                <div class="input-group">
                    <label class="form-label">Name</label>
                    <input type="text" value="<?php echo sanitize($currentUser['name']); ?>" disabled>
                </div>
                <div class="input-group">
                    <label class="form-label">Email</label>
                    <input type="text" value="<?php echo sanitize($currentUser['email']); ?>" disabled>
                </div>
                <div class="textarea-group">
                    <label class="form-label" for="delivery_address">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="4"><?php echo sanitize($_POST['delivery_address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="button button-primary">Confirm Order</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Order summary</h3>
            <table class="table">
                <thead>
                    <tr><th>Product</th><th>Qty</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cartProducts as $product): ?>
                        <tr>
                            <td><?php echo sanitize($product['name']); ?></td>
                            <td><?php echo sanitize($product['quantity']); ?></td>
                            <td><?php echo formatCurrency((float)$product['subtotal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2" style="text-align:right;font-weight:700;">Grand Total</td>
                        <td><?php echo formatCurrency(cartTotal()); ?></td>
                    </tr>
                </tbody>
            </table>
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
