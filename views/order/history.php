<section class="section-title">
    <h2>My Orders</h2>
</section>

<?php if (empty($orders)): ?>
    <div class="card"><div class="card-body"><p>You have not placed any orders yet.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Total</th>
                <th>Status</th>
                <th>Placed</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo sanitize($order['id']); ?></td>
                    <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                    <td><?php echo sanitize($order['status']); ?></td>
                    <td><?php echo sanitize($order['created_at']); ?></td>
                    <td><a href="index.php?route=order&action=success&id=<?php echo sanitize($order['id']); ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
