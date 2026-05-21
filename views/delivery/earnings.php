<section class="section-title">
    <h2>💵 Earnings</h2>
    <a href="index.php?route=delivery" class="btn-secondary">← Back</a>
</section>

<div class="grid grid-4" style="margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo formatCurrency($summary['today'] ?? 0); ?></div>
        <div class="stat-label">Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📆</div>
        <div class="stat-value"><?php echo formatCurrency($summary['week'] ?? 0); ?></div>
        <div class="stat-label">This Week</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗓️</div>
        <div class="stat-value"><?php echo formatCurrency($summary['month'] ?? 0); ?></div>
        <div class="stat-label">This Month</div>
    </div>
    <div class="stat-card" style="border:2px solid #27ae60;">
        <div class="stat-icon">💰</div>
        <div class="stat-value" style="color:#27ae60;"><?php echo formatCurrency($summary['all_time'] ?? 0); ?></div>
        <div class="stat-label">All Time</div>
    </div>
</div>

<div class="card" style="margin-bottom:28px;">
    <div class="card-body">
        <p style="margin:0;">Total completed deliveries: <strong><?php echo (int)($earnings['deliveries'] ?? 0); ?></strong>. Your earning rate is <strong>10%</strong> per order.</p>
    </div>
</div>

<section class="section-title"><h3>Recent Completed Deliveries</h3></section>

<?php if (empty($recentDeliveries)): ?>
    <div class="card"><div class="card-body empty-state">
        <div style="font-size:2.5rem;">📭</div>
        <p>No completed deliveries yet.</p>
    </div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Order #</th><th>Customer</th><th>Order Total</th><th>Your Earning (10%)</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach ($recentDeliveries as $order): ?>
                <tr>
                    <td>#<?php echo sanitize($order['id']); ?></td>
                    <td><?php echo sanitize($order['customer']); ?></td>
                    <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                    <td style="color:#27ae60;font-weight:600;"><?php echo formatCurrency($order['total_amount'] * 0.10); ?></td>
                    <td><?php echo sanitize($order['updated_at'] ?? $order['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>