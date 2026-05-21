<section class="section-title">
    <h2>Order Placed</h2>
</section>

<div class="alert alert-success">
    <strong>Success!</strong> Your order has been placed successfully.
</div>
<div class="card">
    <div class="card-body">
        <p><strong>Order ID:</strong> <?php echo sanitize($order['id']); ?></p>
        <p><strong>Status:</strong> <?php echo sanitize($order['status']); ?></p>
        <?php if (isset($order['subtotal'])): ?>
            <p><strong>Subtotal:</strong> <?php echo formatCurrency((float)$order['subtotal']); ?></p>
            <p><strong>Delivery fee:</strong> <?php echo formatCurrency((float)$order['delivery_fee']); ?></p>
        <?php endif; ?>
        <p><strong>Total:</strong> <?php echo formatCurrency((float)$order['total_amount']); ?></p>
        <?php if (!empty($order['payment_method'])): ?>
            <p><strong>Payment:</strong> <?php echo sanitize(str_replace('_', ' ', $order['payment_method'])); ?></p>
        <?php endif; ?>
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

<?php if (!$reviewExists): ?>
    <section class="section-title" style="margin-top:24px;">
        <h2>Leave a Review</h2>
    </section>
    <?php if ($reviewMessage): ?>
        <div class="alert <?php echo $reviewSubmitted ? 'alert-success' : 'alert-error'; ?>">
            <?php echo sanitize($reviewMessage); ?>
        </div>
    <?php endif; ?>
    <?php if (!$reviewSubmitted): ?>
        <div class="card">
            <div class="card-body">
                <form method="post" action="index.php?route=order&action=success&id=<?php echo sanitize($order['id']); ?>">
                    <div class="input-group">
                        <label class="form-label">Rating</label>
                        <select name="rating" >
                            <option value="">Select rating</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="textarea-group">
                        <label class="form-label">Comment</label>
                        <textarea name="comment" rows="4" ></textarea>
                    </div>
                    <button type="submit" class="button button-primary">Submit Review</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-success">You have already submitted a review for this order.</div>
<?php endif; ?>

<a class="button button-primary" href="index.php?route=order">View My Orders</a>
