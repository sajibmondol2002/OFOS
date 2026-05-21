<section class="section-title">
    <h2>Customer Accounts</h2>
</section>
<form method="get" action="index.php" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <input type="hidden" name="route" value="admin">
    <input type="hidden" name="action" value="customers">
    <input type="search" name="q" placeholder="Search name or email" value="<?php echo sanitize($q); ?>">
    <button class="button" type="submit">Search</button>
    <?php if ($q !== ''): ?> <a class="button" href="index.php?route=admin&action=customers">Clear</a><?php endif; ?>
</form>
<?php if (empty($users)): ?>
    <div class="card"><div class="card-body"><p>No customers found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo sanitize($u['name']); ?></td>
                    <td><?php echo sanitize($u['email']); ?></td>
                    <td><?php echo sanitize($u['status']); ?></td>
                    <td><?php echo sanitize($u['created_at']); ?></td>
                    <td>
                        <a class="button" href="index.php?route=admin&action=view_user&id=<?php echo sanitize($u['id']); ?>">View</a>
                        <form method="post" action="index.php?route=admin&action=customers" style="display:inline-flex; gap:8px; align-items:center; margin-left:8px;">
                            <input type="hidden" name="user_id" value="<?php echo sanitize($u['id']); ?>">
                            <select name="status">
                                <option value="active" <?php echo $u['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $u['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <button type="submit" class="button">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
