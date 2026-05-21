<?php
require_once __DIR__ . '/../inc/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

requireAdmin();

$orderModel = new Order();

// Total revenue (delivered orders)
$totalRevenueRow = db_fetch_one("SELECT IFNULL(SUM(total_amount),0) AS total FROM orders WHERE status = 'delivered'");
$totalRevenue = $totalRevenueRow['total'] ?? 0;

// Orders per status
$ordersByStatus = db_fetch_all("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");

// Busiest delivery agents (by deliveries)
$busiestAgents = db_fetch_all(
    "SELECT u.id, u.name, COUNT(*) AS deliveries FROM orders o JOIN users u ON u.id = o.delivery_agent_id WHERE o.delivery_status = 'delivered' GROUP BY o.delivery_agent_id ORDER BY deliveries DESC LIMIT 10"
);

// Peak ordering hours (top 6 hours)
$peakHours = db_fetch_all(
    "SELECT HOUR(created_at) AS hour, COUNT(*) AS cnt FROM orders GROUP BY hour ORDER BY cnt DESC LIMIT 6"
);

// Delivery performance: avg delivery time (minutes), on-time rate, failed deliveries
$avgDeliveryRow = db_fetch_one("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) AS avg_minutes FROM orders WHERE delivered_at IS NOT NULL");
$avgDeliveryMins = $avgDeliveryRow['avg_minutes'] ? round((float)$avgDeliveryRow['avg_minutes'],2) : 0;

// on-time threshold from settings (fallback 30 mins)
$onTimeThreshold = (int)(getSetting('on_time_threshold_minutes') ?? 30);
$onTimeRow = db_fetch_one("SELECT SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, created_at, delivered_at) <= ? THEN 1 ELSE 0 END) AS ontime, COUNT(*) AS total FROM orders WHERE delivered_at IS NOT NULL", 'i', [$onTimeThreshold]);
$onTimeCount = (int)($onTimeRow['ontime'] ?? 0);
$onTimeTotal = (int)($onTimeRow['total'] ?? 0);
$onTimeRate = $onTimeTotal > 0 ? round($onTimeCount / $onTimeTotal * 100, 2) : 0;

$failedDeliveriesRow = db_fetch_one("SELECT COUNT(*) AS failed FROM orders WHERE status = 'cancelled'");
$failedDeliveries = (int)($failedDeliveriesRow['failed'] ?? 0);

// Monthly summary (last 6 months)
$monthly = db_fetch_all(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS orders, SUM(total_amount) AS revenue, AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) AS avg_delivery_mins FROM orders GROUP BY month ORDER BY month DESC LIMIT 6"
);

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Analytics</title>
    <link rel="stylesheet" href="/Food/assets/css/style.css">
    <style> .card{border:1px solid #ddd;padding:12px;margin:8px;border-radius:6px;} .grid{display:flex;gap:12px;flex-wrap:wrap;} </style>
</head>
<body>
<?php include __DIR__ . '/../views/partials/header.php'; ?>
<main class="container">
    <h2>Platform Analytics</h2>
    <div class="grid">
        <div class="card">
            <h3>Total Revenue</h3>
            <p>BDT <?php echo number_format((float)$totalRevenue,2); ?></p>
        </div>
        <div class="card">
            <h3>Avg Delivery Time</h3>
            <p><?php echo $avgDeliveryMins; ?> minutes</p>
            <p>On-time rate: <?php echo $onTimeRate; ?>% (threshold <?php echo $onTimeThreshold; ?> mins)</p>
        </div>
        <div class="card">
            <h3>Failed Deliveries</h3>
            <p><?php echo $failedDeliveries; ?></p>
        </div>
    </div>

    <h3>Orders by Status</h3>
    <ul>
        <?php foreach ($ordersByStatus as $s): ?>
            <li><?php echo htmlspecialchars($s['status']); ?>: <?php echo $s['cnt']; ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Busiest Delivery Agents</h3>
    <ol>
        <?php foreach ($busiestAgents as $a): ?>
            <li><?php echo htmlspecialchars($a['name']); ?> — <?php echo $a['deliveries']; ?> deliveries</li>
        <?php endforeach; ?>
    </ol>

    <h3>Peak Ordering Hours</h3>
    <ul>
        <?php foreach ($peakHours as $h): ?>
            <li><?php echo $h['hour']; ?>:00 — <?php echo $h['cnt']; ?> orders</li>
        <?php endforeach; ?>
    </ul>

    <h3>Monthly Summary (latest 6)</h3>
    <table>
        <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th><th>Avg Delivery (mins)</th></tr></thead>
        <tbody>
        <?php foreach ($monthly as $m): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['month']); ?></td>
                <td><?php echo $m['orders']; ?></td>
                <td>BDT <?php echo number_format((float)$m['revenue'],2); ?></td>
                <td><?php echo $m['avg_delivery_mins'] ? round($m['avg_delivery_mins'],2) : 'N/A'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
</body>
</html>
