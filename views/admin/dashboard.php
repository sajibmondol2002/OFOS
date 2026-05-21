<section class="section-title">
    <h2>Admin Dashboard</h2>
</section>

<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card"><div class="card-body"><h3>Active Restaurants</h3><p><?php echo sanitize($totalActiveRestaurants); ?> active restaurants</p></div></div>
    <div class="card"><div class="card-body"><h3>Orders Today</h3><p><?php echo sanitize($totalOrdersToday); ?> orders placed today</p></div></div>
    <div class="card"><div class="card-body"><h3>Registered Users</h3><p><?php echo sanitize($totalRegisteredUsers); ?> total registered users</p></div></div>
    <div class="card"><div class="card-body"><h3>Active Delivery Agents</h3><p><?php echo sanitize($totalActiveDeliveryAgents); ?> available agents</p></div></div>
</div>

<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card"><div class="card-body"><h3>Products</h3><p><?php echo sanitize($totalProducts); ?> active menu items</p></div></div>
    <div class="card"><div class="card-body"><h3>Categories</h3><p><?php echo sanitize($totalCategories); ?> categories available</p></div></div>
    <div class="card"><div class="card-body"><h3>Orders</h3><p><?php echo sanitize($totalOrders); ?> total orders</p></div></div>
    <div class="card"><div class="card-body"><h3>Quick Links</h3>
        <p><a href="index.php?route=admin&action=orders">Review orders</a></p>
        <p><a href="index.php?route=admin&action=users">Manage users</a></p>
        <p><a href="index.php?route=admin&action=products">Manage products</a></p>
        <p><a href="index.php?route=admin&action=restaurants">Manage restaurants</a></p>
        <p><a href="index.php?route=admin&action=customers">Customers</a></p>
        <p><a href="index.php?route=admin&action=delivery_agents">Delivery agents</a></p>
        <p><a href="index.php?route=admin&action=complaints">Complaints</a></p>
        <p><a href="index.php?route=admin&action=featured">Featured restaurants</a></p>
        <p><a href="index.php?route=admin&action=analytics">Analytics</a></p>
        <p><a href="index.php?route=admin&action=settings">Settings</a></p>
    </div></div>
</div>

<section class="section-title">
    <h2>Recent Orders</h2>
</section>

<?php if (empty($recentOrders)): ?>
    <div class="card"><div class="card-body"><p>No recent orders found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th></tr>
        </thead>
        <tbody>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?php echo sanitize($order['id']); ?></td>
                    <td><?php echo sanitize($order['customer']); ?></td>
                    <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                    <td><?php echo sanitize($order['status']); ?></td>
                    <td><?php echo sanitize($order['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
