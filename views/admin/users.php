<section class="section-title">
    <h2>User Management</h2>
</section>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>

<?php if (empty($users)): ?>
    <div class="card"><div class="card-body"><p>No users found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo sanitize($user['name']); ?></td>
                    <td><?php echo sanitize($user['email']); ?></td>
                    <td><?php echo sanitize($user['role']); ?></td>
                    <td><?php echo sanitize($user['status']); ?></td>
                    <td><?php echo sanitize($user['created_at']); ?></td>
                    <td>
                        <form method="post" action="index.php?route=admin&action=users" style="display:inline-flex; gap:8px;">
                            <input type="hidden" name="user_id" value="<?php echo sanitize($user['id']); ?>">
                            <select name="status">
                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Approve / Active</option>
                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive / Pending</option>
                            </select>
                            <button type="submit" class="button button-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
