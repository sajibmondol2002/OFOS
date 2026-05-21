<section class="section-title">
    <h2>Your Cart</h2>
</section>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>

<?php if (empty($cartProducts)): ?>
    <div class="card"><div class="card-body"><p>Your cart is empty. <a href="index.php?route=menu">Browse menu</a></p></div></div>
<?php else: ?>
    <form method="post" action="index.php?route=cart">
        <input type="hidden" name="action" value="update">
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartProducts as $product): ?>
                    <tr>
                        <td>
                            <?php echo sanitize($product['name']); ?>
                            <?php if ((float) ($product['discount_pct'] ?? 0) > 0): ?>
                                <br><small><?php echo sanitize($product['discount_pct']); ?>% discount applied</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency((float)$product['price']); ?></td>
                        <td><input type="number" name="quantities[<?php echo sanitize($product['id']); ?>]" value="<?php echo sanitize($product['quantity']); ?>" min="0" style="width:80px"></td>
                        <td><?php echo formatCurrency((float)$product['subtotal']); ?></td>
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
            <a class="button" href="index.php?route=order&action=checkout">Checkout</a>
        </div>
    </form>
<?php endif; ?>
