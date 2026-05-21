<section class="section-title">
    <h2><?php echo sanitize($restaurant['name']); ?></h2>
    <a class="button button-primary" href="index.php?route=restaurant&action=profile">Edit Profile</a>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if ((int) ($restaurant['is_approved'] ?? 0) !== 1): ?>
    <div class="alert alert-error">This restaurant is waiting for platform admin approval.</div>
<?php endif; ?>

<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card"><div class="card-body"><h3>Open Status</h3><p><?php echo (int) $restaurant['is_open'] === 1 ? 'Open' : 'Closed'; ?></p></div></div>
    <div class="card"><div class="card-body"><h3>Menu Items</h3><p><?php echo sanitize($totalItems); ?> items</p></div></div>
    <div class="card"><div class="card-body"><h3>Categories</h3><p><?php echo sanitize($totalCategories); ?> categories</p></div></div>
    <div class="card"><div class="card-body"><h3>Active Orders</h3><p><?php echo sanitize(count($activeOrders)); ?> in progress</p></div></div>
    <div class="card"><div class="card-body"><h3>Total Orders</h3><p><?php echo sanitize($summary['total_orders'] ?? 0); ?></p></div></div>
    <div class="card"><div class="card-body"><h3>Revenue</h3><p><?php echo formatCurrency((float) ($summary['total_revenue'] ?? 0)); ?></p></div></div>
</div>

<section class="section-title">
    <h2>Active Orders</h2>
    <a class="button" href="index.php?route=restaurant&action=orders">Manage Orders</a>
</section>

<?php if (empty($groupedOrders)): ?>
    <div class="card"><div class="card-body"><p>No active orders right now.</p></div></div>
<?php else: ?>
    <?php foreach ($groupedOrders as $status => $orders): ?>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
                <h3><?php echo sanitize(ucwords(str_replace('_', ' ', $status))); ?></h3>
                <table class="table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Placed</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo sanitize($order['id']); ?></td>
                                <td><?php echo sanitize($order['customer']); ?></td>
                                <td><?php echo formatCurrency((float) $order['total_amount']); ?></td>
                                <td><?php echo sanitize($order['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

