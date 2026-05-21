<section class="section-title">
    <h2>User Details</h2>
</section>
<div class="card">
    <div class="card-body">
        <h3><?php echo sanitize($user['name']); ?></h3>
        <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo sanitize($user['phone']); ?></p>
        <p><strong>Role:</strong> <?php echo sanitize($user['role']); ?></p>
        <p><strong>Status:</strong> <?php echo sanitize($user['status']); ?></p>
        <?php if (!empty($user['vehicle_type'])): ?>
            <p><strong>Vehicle type:</strong> <?php echo sanitize($user['vehicle_type']); ?></p>
        <?php endif; ?>
        <?php if (isset($user['is_available'])): ?>
            <p><strong>Available:</strong> <?php echo (int)$user['is_available'] === 1 ? 'Yes' : 'No'; ?></p>
        <?php endif; ?>
        <p><strong>Joined:</strong> <?php echo sanitize($user['created_at']); ?></p>
        <div style="margin-top:16px;">
            <a class="button" href="index.php?route=admin&action=customers">Back to customers</a>
        </div>
    </div>
</div>
