<?php
$agentName   = sanitize($agent['name'] ?? 'Agent');
$isAvailable = (int) ($agent['is_available'] ?? 1);
$vehicleType = sanitize($agent['vehicle_type'] ?? 'Not set');
$picturePath = $agent['profile_picture'] ?? null;
$pictureSrc  = $picturePath ? '../' . htmlspecialchars($picturePath, ENT_QUOTES) : null;
?>

<div class="delivery-hero">
    <div class="delivery-hero-left">
        <?php if ($pictureSrc): ?>
            <img src="<?php echo $pictureSrc; ?>" alt="Profile" class="agent-avatar">
        <?php else: ?>
            <div class="agent-avatar-placeholder">
                <?php echo strtoupper(substr($agent['name'] ?? 'A', 0, 1)); ?>
            </div>
        <?php endif; ?>
        <div>
            <h2 style="margin:0 0 4px;">Welcome, <?php echo $agentName; ?>!</h2>
            <p style="margin:0;opacity:.85;">🚗 <?php echo $vehicleType; ?></p>
        </div>
    </div>
    <span class="badge-status <?php echo $isAvailable ? 'badge-online' : 'badge-offline'; ?>">
        <?php echo $isAvailable ? '🟢 Online' : '🔴 Offline'; ?>
    </span>
</div>

<div class="grid grid-3" style="margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?php echo count($availableOrders); ?></div>
        <div class="stat-label">Available Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🚚</div>
        <div class="stat-value"><?php echo count($myActiveDeliveries); ?></div>
        <div class="stat-label">Active Deliveries</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value"><?php echo formatCurrency($earnings['total'] ?? 0); ?></div>
        <div class="stat-label">Total Earnings</div>
    </div>
</div>

<div class="delivery-nav">
    <a href="index.php?route=delivery&action=assignments" class="delivery-nav-btn">📋 Assignments</a>
    <a href="index.php?route=delivery&action=earnings"    class="delivery-nav-btn">💵 Earnings</a>
    <a href="index.php?route=delivery&action=history"     class="delivery-nav-btn">🕓 History</a>
    <a href="index.php?route=delivery&action=profile"     class="delivery-nav-btn">👤 My Profile</a>
</div>

<section class="section-title"><h2>Available Orders to Accept</h2></section>

<?php if (empty($availableOrders)): ?>
    <div class="card"><div class="card-body empty-state">
        <div style="font-size:2.5rem;">🎉</div>
        <p>No new orders right now. Check back soon!</p>
    </div></div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($availableOrders as $order): ?>
            <div class="order-card">
                <div class="order-card-header">
                    <span class="order-id">#<?php echo sanitize($order['id']); ?></span>
                    <span class="order-time"><?php echo sanitize($order['created_at']); ?></span>
                </div>
                <div class="order-card-body">
                    <p><strong>👤 Customer:</strong> <?php echo sanitize($order['customer']); ?></p>
                    <p><strong>📍 Address:</strong> <?php echo sanitize($order['delivery_address']); ?></p>
                    <p><strong>💵 Total:</strong> <?php echo formatCurrency((float)$order['total_amount']); ?>
                       <em style="color:#27ae60;"> (+<?php echo formatCurrency($order['total_amount'] * 0.10); ?> earning)</em>
                    </p>
                </div>
                <div class="order-card-footer">
                    <form method="post" action="index.php?route=delivery&action=assignments">
                        <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="btn-accept">✅ Accept Order</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($myActiveDeliveries)): ?>
<section class="section-title" style="margin-top:32px;"><h2>My Active Deliveries</h2></section>
<div class="order-list">
    <?php foreach ($myActiveDeliveries as $order):
        if (($order['delivery_status'] ?? '') === 'delivered') continue;
        $status = $order['delivery_status'] ?? 'pending';
        $statusMap = ['pending'=>'⏳ Pending','picked_up'=>'📦 Picked Up','on_the_way'=>'🚗 On the Way','delivered'=>'✅ Delivered'];
    ?>
        <div class="order-card">
            <div class="order-card-header">
                <span class="order-id">#<?php echo sanitize($order['id']); ?></span>
                <span class="status-badge status-<?php echo sanitize($status); ?>"><?php echo $statusMap[$status] ?? $status; ?></span>
            </div>
            <div class="order-card-body">
                <p><strong>👤</strong> <?php echo sanitize($order['customer']); ?></p>
                <p><strong>📍</strong> <?php echo sanitize($order['delivery_address']); ?></p>
            </div>
            <div class="order-card-footer">
                <form method="post" action="index.php?route=delivery&action=assignments" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                    <input type="hidden" name="action" value="update_status">
                    <select name="delivery_status" class="status-select">
                        <option value="pending"    <?php echo $status==='pending'    ?'selected':''; ?>>⏳ Pending</option>
                        <option value="picked_up"  <?php echo $status==='picked_up'  ?'selected':''; ?>>📦 Picked Up</option>
                        <option value="on_the_way" <?php echo $status==='on_the_way' ?'selected':''; ?>>🚗 On the Way</option>
                        <option value="delivered"  <?php echo $status==='delivered'  ?'selected':''; ?>>✅ Delivered</option>
                    </select>
                    <button type="submit" class="btn-primary">Update</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>