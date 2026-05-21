<section class="section-title">
    <h2>Restaurant Orders</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="alert alert-success" id="orderFeedNotice" style="display:none;"></div>

<section class="section-title">
    <h2>Live Active Orders</h2>
    <span id="lastRefresh" style="font-size:.95rem;"></span>
</section>
<div id="activeOrders">
    <div class="card"><div class="card-body"><p>Loading active orders...</p></div></div>
</div>

<section class="section-title" style="margin-top:32px;">
    <h2>Full Order History</h2>
</section>

<?php if (empty($orders)): ?>
    <div class="card"><div class="card-body"><p>No orders found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Delivery</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo sanitize($order['id']); ?><br><small><?php echo sanitize($order['created_at']); ?></small></td>
                    <td><?php echo sanitize($order['customer']); ?></td>
                    <td>
                        <?php foreach ($order['items'] as $item): ?>
                            <div><?php echo sanitize($item['quantity']); ?> x <?php echo sanitize($item['name']); ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td><?php echo formatCurrency((float) $order['total_amount']); ?></td>
                    <td><?php echo sanitize(ucwords(str_replace('_', ' ', $order['status']))); ?></td>
                    <td><?php echo sanitize($order['delivery_status'] ?? 'not assigned'); ?></td>
                    <td>
                        <?php if (!in_array($order['status'], ['delivered', 'cancelled'], true)): ?>
                            <form method="post" action="index.php?route=restaurant&action=orders" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                                <select name="status">
                                    <option value="accepted" <?php echo $order['status'] === 'accepted' ? 'selected' : ''; ?>>Accept</option>
                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Reject / Cancel</option>
                                </select>
                                <button type="submit" class="button">Save</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
let knownOrderIds = new Set();

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char]);
    });
}

function renderOrderItems(items) {
    if (!items || items.length === 0) {
        return '<em>No items found</em>';
    }
    return items.map(item => `<div>${escapeHtml(item.quantity)} x ${escapeHtml(item.name)}</div>`).join('');
}

function renderActiveOrders(orders) {
    const container = document.getElementById('activeOrders');
    const notice = document.getElementById('orderFeedNotice');
    const newOrders = orders.filter(order => !knownOrderIds.has(String(order.id)));
    orders.forEach(order => knownOrderIds.add(String(order.id)));

    if (newOrders.length > 0 && knownOrderIds.size > newOrders.length) {
        notice.textContent = `${newOrders.length} new active order${newOrders.length === 1 ? '' : 's'} received.`;
        notice.style.display = 'block';
        setTimeout(() => notice.style.display = 'none', 5000);
    }

    if (orders.length === 0) {
        container.innerHTML = '<div class="card"><div class="card-body"><p>No active orders right now.</p></div></div>';
        return;
    }

    container.innerHTML = `<table class="table">
        <thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
            ${orders.map(order => `<tr>
                <td>#${escapeHtml(order.id)}</td>
                <td>${escapeHtml(order.customer)}</td>
                <td>${renderOrderItems(order.items)}</td>
                <td>${Number(order.total_amount || 0).toFixed(2)}</td>
                <td>${escapeHtml(String(order.status || '').replaceAll('_', ' '))}</td>
            </tr>`).join('')}
        </tbody>
    </table>`;
}

function refreshOrders() {
    fetch('index.php?route=restaurant&action=orders_feed', { credentials: 'same-origin' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderActiveOrders(result.orders || []);
                document.getElementById('lastRefresh').textContent = 'Last refreshed: ' + new Date().toLocaleTimeString();
            }
        })
        .catch(() => {
            document.getElementById('activeOrders').innerHTML = '<div class="card"><div class="card-body"><p>Could not refresh live orders.</p></div></div>';
        });
}

refreshOrders();
setInterval(refreshOrders, 10000);
</script>

