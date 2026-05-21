<section class="section-title">
    <h2>Sales Analytics</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card"><div class="card-body"><h3>Total Orders</h3><p><?php echo sanitize($summary['total_orders'] ?? 0); ?></p></div></div>
    <div class="card"><div class="card-body"><h3>Total Revenue</h3><p><?php echo formatCurrency((float) ($summary['total_revenue'] ?? 0)); ?></p></div></div>
    <div class="card"><div class="card-body"><h3>Average Order Value</h3><p><?php echo formatCurrency((float) ($summary['average_order_value'] ?? 0)); ?></p></div></div>
</div>

<section class="section-title">
    <h2>Orders by Day</h2>
</section>
<?php if (empty($ordersByDay)): ?>
    <div class="card"><div class="card-body"><p>No daily order data available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php foreach ($ordersByDay as $row): ?>
                <tr>
                    <td><?php echo sanitize($row['period']); ?></td>
                    <td><?php echo sanitize($row['orders']); ?></td>
                    <td><?php echo formatCurrency((float) $row['revenue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Orders by Week</h2>
</section>
<?php if (empty($ordersByWeek)): ?>
    <div class="card"><div class="card-body"><p>No weekly order data available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Year Week</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php foreach ($ordersByWeek as $row): ?>
                <tr>
                    <td><?php echo sanitize($row['period']); ?></td>
                    <td><?php echo sanitize($row['orders']); ?></td>
                    <td><?php echo formatCurrency((float) $row['revenue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Orders by Month</h2>
</section>
<?php if (empty($ordersByMonth)): ?>
    <div class="card"><div class="card-body"><p>No monthly order data available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php foreach ($ordersByMonth as $row): ?>
                <tr>
                    <td><?php echo sanitize($row['period']); ?></td>
                    <td><?php echo sanitize($row['orders']); ?></td>
                    <td><?php echo formatCurrency((float) $row['revenue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Most Ordered Items</h2>
</section>
<?php if (empty($topItems)): ?>
    <div class="card"><div class="card-body"><p>No item data available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Item</th><th>Quantity Ordered</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php foreach ($topItems as $item): ?>
                <tr>
                    <td><?php echo sanitize($item['name']); ?></td>
                    <td><?php echo sanitize($item['total_quantity']); ?></td>
                    <td><?php echo formatCurrency((float) $item['revenue']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Discount Performance</h2>
</section>
<?php if (empty($discountPerformance)): ?>
    <div class="card"><div class="card-body"><p>No discount campaign data available.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Item</th><th>Discount</th><th>Valid</th><th>Status</th><th>Orders Used</th><th>Items Sold</th></tr></thead>
        <tbody>
            <?php foreach ($discountPerformance as $discount): ?>
                <tr>
                    <td><?php echo sanitize($discount['item_name']); ?></td>
                    <td><?php echo sanitize($discount['discount_pct']); ?>%</td>
                    <td><?php echo sanitize($discount['valid_from']); ?> to <?php echo sanitize($discount['valid_until']); ?></td>
                    <td><?php echo (int) $discount['is_active'] === 1 ? 'Active' : 'Inactive'; ?></td>
                    <td><?php echo sanitize($discount['orders_used']); ?></td>
                    <td><?php echo sanitize($discount['items_sold']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

