<section class="section-title">
    <h2>🕓 Delivery History</h2>
    <a href="index.php?route=delivery" class="btn-secondary">← Back</a>
</section>

<?php if (empty($history)): ?>
    <div class="card"><div class="card-body empty-state">
        <div style="font-size:2.5rem;">📭</div>
        <p>No completed deliveries yet.</p>
    </div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Order #</th><th>Customer</th><th>Address</th><th>Total</th><th>Earned</th><th>Date</th></tr></thead>
        <tbody>
            <?php foreach ($history as $order): ?>
                <tr>
                    <td>#<?php echo sanitize($order['id']); ?></td>
                    <td><?php echo sanitize($order['customer']); ?></td>
                    <td><?php echo sanitize($order['delivery_address']); ?></td>
                    <td><?php echo formatCurrency((float)$order['total_amount']); ?></td>
                    <td style="color:#27ae60;font-weight:600;"><?php echo formatCurrency($order['total_amount'] * 0.10); ?></td>
                    <td><?php echo sanitize($order['updated_at'] ?? $order['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight:700;background:#f0faf4;">
                <td colspan="4">Total Earned</td>
                <td style="color:#27ae60;"><?php echo formatCurrency(array_sum(array_map(fn($o) => $o['total_amount'] * 0.10, $history))); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>