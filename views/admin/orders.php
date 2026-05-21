<section class="section-title">
    <h2>Order Management</h2>
</section>

<?php if (empty($orders)): ?>
    <div class="card"><div class="card-body"><p>No orders available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo sanitize($order['id']); ?></td>
                    <td><?php echo sanitize($order['customer']); ?></td>
                    <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                    <td><?php echo sanitize($order['status']); ?></td>
                    <td><?php echo sanitize($order['created_at']); ?></td>
                    <td>
                        <form method="post" action="index.php?route=admin&action=orders" style="display:inline-flex; gap:8px; align-items:center;">
                            <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                            <select name="status">
                                <?php foreach (['pending','accepted','preparing','ready','picked_up','delivered','cancelled'] as $statusOption): ?>
                                    <option value="<?php echo sanitize($statusOption); ?>" <?php echo $statusOption === $order['status'] ? 'selected' : ''; ?>><?php echo sanitize(ucwords(str_replace('_', ' ', $statusOption))); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
