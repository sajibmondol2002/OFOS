<section class="section-title">
    <h2>Restaurant Accounts</h2>
</section>

<?php if (empty($restaurants)): ?>
    <div class="card"><div class="card-body"><p>No restaurant manager accounts found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Restaurant</th>
                <th>Manager</th>
                <th>Location</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $restaurant): ?>
                <tr>
                    <td>
                        <strong><?php echo sanitize($restaurant['restaurant_name'] ?? 'Profile not submitted'); ?></strong>
                        <div><?php echo sanitize($restaurant['cuisine_type'] ?? ''); ?></div>
                    </td>
                    <td>
                        <?php echo sanitize($restaurant['manager_name']); ?><br>
                        <small><?php echo sanitize($restaurant['email']); ?></small>
                    </td>
                    <td><?php echo sanitize(trim(($restaurant['address'] ?? '') . ', ' . ($restaurant['city'] ?? ''), ', ')); ?></td>
                    <td>
                        User: <?php echo sanitize($restaurant['status']); ?><br>
                        Restaurant: <?php echo (int) ($restaurant['is_approved'] ?? 0) === 1 ? 'approved' : 'pending'; ?><br>
                        <?php echo (int) ($restaurant['is_open'] ?? 0) === 1 ? 'open' : 'closed'; ?>
                    </td>
                    <td><?php echo sanitize($restaurant['created_at']); ?></td>
                    <td>
                        <form method="post" action="index.php?route=admin&action=restaurants" style="display:inline-flex; gap:8px; align-items:center;">
                            <input type="hidden" name="restaurant_id" value="<?php echo sanitize($restaurant['id']); ?>">
                            <select name="status">
                                <option value="active" <?php echo $restaurant['status'] === 'active' ? 'selected' : ''; ?>>Approve / Active</option>
                                <option value="inactive" <?php echo $restaurant['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive / Pending</option>
                            </select>
                            <button type="submit" class="button">Save</button>
                        </form>
                        <form method="post" action="index.php?route=admin&action=restaurants" style="display:inline;" onsubmit="return confirm('Reject and delete this restaurant manager?');">
                            <input type="hidden" name="reject_id" value="<?php echo sanitize($restaurant['id']); ?>">
                            <button type="submit" class="button button-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

