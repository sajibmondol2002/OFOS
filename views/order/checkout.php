<section class="section-title">
    <h2>Checkout</h2>
</section>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="form-card">
        <h3>Delivery details</h3>
        <form method="post" action="index.php?route=order&action=checkout">
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
                <textarea id="delivery_address" name="delivery_address" rows="4"><?php echo sanitize($deliveryAddress); ?></textarea>
            </div>
            <div class="input-group">
                <label class="form-label" for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method">
                    <option value="cash_on_delivery" <?php echo ($paymentMethod ?? '') === 'cash_on_delivery' ? 'selected' : ''; ?>>Cash on Delivery</option>
                    <option value="card" <?php echo ($paymentMethod ?? '') === 'card' ? 'selected' : ''; ?>>Card</option>
                    <option value="mobile_banking" <?php echo ($paymentMethod ?? '') === 'mobile_banking' ? 'selected' : ''; ?>>Mobile Banking</option>
                </select>
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
                    <td colspan="2" style="text-align:right;font-weight:700;">Subtotal</td>
                    <td><?php echo formatAsBdt(cartTotal()); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;font-weight:700;">Delivery Fee</td>
                    <td><?php echo formatAsBdt((float) (getSetting('base_delivery_fee') ?? 20)); ?></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;font-weight:700;">Grand Total</td>
                    <td><?php echo formatAsBdt(cartTotal() + (float) (getSetting('base_delivery_fee') ?? 20)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
