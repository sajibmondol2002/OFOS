<?php
require_once __DIR__ . '/inc/functions.php';
requireLogin();

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        if ($productId > 0) {
            addToCart($productId, 1);
            $message = 'Product added to cart.';
        }
    } elseif ($action === 'update') {
        $quantities = [];
        foreach ($_POST['quantities'] ?? [] as $productId => $quantity) {
            $quantities[(int)$productId] = (int)$quantity;
        }
        updateCart($quantities);
        $message = 'Cart updated successfully.';
    }
} elseif (isset($_GET['remove'])) {
    $productId = (int) $_GET['remove'];
    if ($productId > 0) {
        updateCart([$productId => 0]);
        $message = 'Item removed from cart.';
    }
}

$cartProducts = getCartProducts();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Online Food Ordering</title>
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
            <?php if ($currentUser): ?>
                <a href="order_history.php">My Orders</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Your Cart</h2>
    </section>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <?php if (empty($cartProducts)): ?>
        <div class="card"><div class="card-body"><p>Your cart is empty. <a href="menu.php">Browse menu</a></p></div></div>
    <?php else: ?>
        <form method="post" action="cart.php">
            <input type="hidden" name="action" value="update">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartProducts as $product): ?>
                                <tr>
                            <td><?php echo sanitize($product['name']); ?></td>
                            <td><?php echo formatCurrency((float)$product['price']); ?></td>
                            <td><input type="number" name="quantities[<?php echo sanitize($product['id']); ?>]" value="<?php echo sanitize($product['quantity']); ?>" min="0" style="width:80px"></td>
                            <td><?php echo formatCurrency((float)$product['subtotal']); ?></td>
                            <td><a class="button button-secondary" href="cart.php?remove=<?php echo sanitize($product['id']); ?>">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:700;">Total</td>
                        <td><?php echo formatCurrency(cartTotal()); ?></td>
                    </tr>
                </tbody>
            </table>
            <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
                <button type="submit" class="button button-primary">Update Cart</button>
                <a class="button" href="checkout.php">Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
