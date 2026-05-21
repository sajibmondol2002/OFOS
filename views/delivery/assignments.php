<?php
$statusMap = [
    'pending'    => ['label'=>'⏳ Pending',    'class'=>'status-pending'],
    'picked_up'  => ['label'=>'📦 Picked Up',  'class'=>'status-picked_up'],
    'on_the_way' => ['label'=>'🚗 On the Way', 'class'=>'status-on_the_way'],
    'delivered'  => ['label'=>'✅ Delivered',  'class'=>'status-delivered'],
];
?>

<section class="section-title">
    <h2>📋 Delivery Assignments</h2>
    <a href="index.php?route=delivery" class="btn-secondary">← Back</a>
</section>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<section class="section-title"><h3>🆕 Available Orders</h3></section>

<?php if (empty($availableOrders)): ?>
    <div class="card"><div class="card-body empty-state">
        <div style="font-size:2.5rem;">📭</div>
        <p>No orders available to accept right now.</p>
    </div></div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($availableOrders as $order): ?>
            <div class="order-card">
                <div class="order-card-header">
                    <span class="order-id">Order #<?php echo sanitize($order['id']); ?></span>
                    <span class="order-time"><?php echo sanitize($order['created_at']); ?></span>
                </div>
                <div class="order-card-body">
                    <p><strong>👤 Customer:</strong> <?php echo sanitize($order['customer']); ?></p>
                    <p><strong>📍 Address:</strong> <?php echo sanitize($order['delivery_address']); ?></p>
                    <p><strong>💵 Total:</strong> <?php echo formatCurrency((float)$order['total_amount']); ?>
                       &nbsp;<span style="color:#27ae60;">You earn: <?php echo formatCurrency($order['total_amount'] * 0.10); ?></span>
                    </p>
                </div>
                <div class="order-card-footer">
                    <form method="post" action="index.php?route=delivery&action=assignments">
                        <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="btn-accept">✅ Accept</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="section-title" style="margin-top:36px;"><h3>🚚 My Active Deliveries</h3></section>

<?php $activeDeliveries = array_filter($myAssignments, fn($o) => ($o['delivery_status'] ?? '') !== 'delivered'); ?>

<?php if (empty($activeDeliveries)): ?>
    <div class="card"><div class="card-body empty-state"><p>You have no active deliveries.</p></div></div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($activeDeliveries as $order):
            $status = $order['delivery_status'] ?? 'pending';
            $info   = $statusMap[$status] ?? ['label'=>$status,'class'=>''];
        ?>
            <div class="order-card">
                <div class="order-card-header">
                    <span class="order-id">Order #<?php echo sanitize($order['id']); ?></span>
                    <span class="status-badge <?php echo $info['class']; ?>"><?php echo $info['label']; ?></span>
                </div>
                <div class="order-card-body">
                    <p><strong>👤</strong> <?php echo sanitize($order['customer']); ?></p>
                    <p><strong>📍</strong> <?php echo sanitize($order['delivery_address']); ?></p>
                    <p><strong>💵</strong> <?php echo formatCurrency((float)$order['total_amount']); ?></p>
                </div>

                <div class="progress-steps">
                    <?php
                    $steps   = ['pending','picked_up','on_the_way','delivered'];
                    $current = array_search($status, $steps);
                    foreach ($steps as $i => $step):
                        $done = $i <= $current;
                    ?>
                        <div class="progress-step <?php echo $done ? 'done' : ''; ?>">
                            <div class="progress-dot"></div>
                            <div class="progress-label"><?php echo $statusMap[$step]['label']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-card-footer">
                    <form method="post" action="index.php?route=delivery&action=assignments" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
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
                    <form method="post" action="index.php?route=delivery&action=assignments" style="margin-top:8px;">
                        <input type="hidden" name="order_id" value="<?php echo sanitize($order['id']); ?>">
                        <input type="hidden" name="action" value="decline">
                        <button type="submit" class="btn-danger" onclick="return confirm('Decline this delivery?')">❌ Decline</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>