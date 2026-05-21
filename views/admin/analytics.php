<section class="section-title">
    <h2>Platform Analytics</h2>
</section>
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="card"><div class="card-body"><h3>Total Revenue</h3><p>BDT <?php echo number_format((float)$totalRevenue,2); ?></p></div></div>
    <div class="card"><div class="card-body"><h3>Average Delivery Time</h3><p><?php echo sanitize($avgDeliveryMins); ?> minutes</p><p>On-time rate: <?php echo sanitize($onTimeRate); ?>% (<?php echo sanitize($onTimeThreshold); ?> mins)</p></div></div>
    <div class="card"><div class="card-body"><h3>Failed Deliveries</h3><p><?php echo sanitize($failedDeliveries); ?></p></div></div>
</div>
<section class="section-title"><h3>Orders by Status</h3></section>
<ul>
    <?php foreach ($ordersByStatus as $s): ?>
        <li><?php echo sanitize($s['status']); ?>: <?php echo sanitize($s['cnt']); ?></li>
    <?php endforeach; ?>
</ul>
<section class="section-title"><h3>Busiest Delivery Agents</h3></section>
<ol>
    <?php foreach ($busiestAgents as $a): ?>
        <li><?php echo sanitize($a['name']); ?> — <?php echo sanitize($a['deliveries']); ?> deliveries</li>
    <?php endforeach; ?>
</ol>
<section class="section-title"><h3>Peak Ordering Hours</h3></section>
<ul>
    <?php foreach ($peakHours as $h): ?>
        <li><?php echo sanitize($h['hour']); ?>:00 — <?php echo sanitize($h['cnt']); ?> orders</li>
    <?php endforeach; ?>
</ul>
<section class="section-title"><h3>Monthly Summary</h3></section>
<table class="table">
    <thead>
        <tr><th>Month</th><th>Orders</th><th>Revenue</th><th>Avg Delivery (mins)</th></tr>
    </thead>
    <tbody>
        <?php foreach ($monthly as $m): ?>
            <tr>
                <td><?php echo sanitize($m['month']); ?></td>
                <td><?php echo sanitize($m['orders']); ?></td>
                <td>BDT <?php echo number_format((float)$m['revenue'],2); ?></td>
                <td><?php echo $m['avg_delivery_mins'] ? sanitize(round($m['avg_delivery_mins'],2)) : 'N/A'; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
