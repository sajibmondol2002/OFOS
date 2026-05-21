<header>
    <div class="container">
        <div class="site-title"><a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=home" style="color:#fff;">Online Food Ordering</a></div>
        <nav>
            <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=home">Home</a>
            <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=menu">Menu</a>
            <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=cart">Cart (<?php echo cartCount(); ?>)</a>
            <?php if (!empty($currentUser)): ?>
                <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                    <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=admin">Admin</a>
                <?php elseif (($currentUser['role'] ?? '') === 'restaurant_manager'): ?>
                    <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=restaurant">Restaurant</a>
                <?php elseif (($currentUser['role'] ?? '') === 'delivery_man'): ?>
                    <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=delivery">Delivery</a>
                <?php else: ?>
                    <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=order">My Orders</a>
                <?php endif; ?>
                <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=auth&action=logout">Logout</a>
            <?php else: ?>
                <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=auth&action=unified">Login</a>
                <a href="<?php echo sanitize($_SERVER['SCRIPT_NAME']); ?>?route=auth&action=unified">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
