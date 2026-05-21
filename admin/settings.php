<?php
require_once __DIR__ . '/../inc/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commission = trim($_POST['commission_rate'] ?? '');
    $base_fee = trim($_POST['base_delivery_fee'] ?? '');
    $per_km = trim($_POST['per_km_fee'] ?? '');
    $formula = trim($_POST['estimated_time_formula'] ?? '');

    // detect columns
    $cols = db_fetch_all("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings'");
    $colNames = array_column($cols, 'COLUMN_NAME');
    $keyCol = in_array('key', $colNames, true) ? 'key' : (in_array('k', $colNames, true) ? 'k' : null);
    $valCol = in_array('value', $colNames, true) ? 'value' : (in_array('v', $colNames, true) ? 'v' : null);
    if ($keyCol === null || $valCol === null) {
        $message = 'Settings table schema not recognised.';
    } else {
        $sql = "INSERT INTO settings (`{$keyCol}`, `{$valCol}`) VALUES (?,?) ON DUPLICATE KEY UPDATE `{$valCol}` = VALUES(`{$valCol}`)";
        db_execute($sql, 'ss', ['commission_rate', $commission]);
        db_execute($sql, 'ss', ['base_delivery_fee', $base_fee]);
        db_execute($sql, 'ss', ['per_km_fee', $per_km]);
        db_execute($sql, 'ss', ['estimated_time_formula', $formula]);
        $message = 'Settings updated.';
    }

    $message = 'Settings updated.';
}

$commission = getSetting('commission_rate');
$base_fee = getSetting('base_delivery_fee');
$per_km = getSetting('per_km_fee');
$formula = getSetting('estimated_time_formula');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Settings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin - Platform Settings</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="featured_restaurants.php">Featured</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title"><h2>Platform Settings</h2></section>
    <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
    <div class="card"><div class="card-body">
        <form method="post" action="settings.php">
            <div class="input-group"><label>Commission rate (%)</label><input type="text" name="commission_rate" value="<?php echo sanitize($commission); ?>"></div>
            <div class="input-group"><label>Base delivery fee</label><input type="text" name="base_delivery_fee" value="<?php echo sanitize($base_fee); ?>"></div>
            <div class="input-group"><label>Per km fee</label><input type="text" name="per_km_fee" value="<?php echo sanitize($per_km); ?>"></div>
            <div class="input-group"><label>Estimated delivery time formula</label><input type="text" name="estimated_time_formula" value="<?php echo sanitize($formula); ?>"></div>
            <button class="button button-primary" type="submit">Save</button>
        </form>
    </div></div>
</main>
<footer><div class="container"><p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p></div></footer>
</body>
</html>
