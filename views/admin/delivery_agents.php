<section class="section-title">
    <h2>Delivery Agents</h2>
</section>
<form method="get" action="index.php" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <input type="hidden" name="route" value="admin">
    <input type="hidden" name="action" value="delivery_agents">
    <input type="search" name="q" placeholder="Search name or email" value="<?php echo sanitize($q); ?>">
    <button class="button" type="submit">Search</button>
    <?php if ($q !== ''): ?> <a class="button" href="index.php?route=admin&action=delivery_agents">Clear</a><?php endif; ?>
</form>
<?php if (empty($agents)): ?>
    <div class="card"><div class="card-body"><p>No delivery agents found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Status</th><th>Available</th><th>Registered</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($agents as $a): ?>
                <tr>
                    <td><?php echo sanitize($a['name']); ?></td>
                    <td><?php echo sanitize($a['email']); ?></td>
                    <td><?php echo sanitize($a['status']); ?></td>
                    <td><?php echo ((int)$a['is_available'] === 1) ? 'Yes' : 'No'; ?></td>
                    <td><?php echo sanitize($a['created_at']); ?></td>
                    <td>
                        <a class="button" href="index.php?route=admin&action=view_user&id=<?php echo sanitize($a['id']); ?>">View</a>
                        <form method="post" action="index.php?route=admin&action=delivery_agents" style="display:inline-flex; gap:8px; align-items:center; margin-left:8px;">
                            <input type="hidden" name="agent_id" value="<?php echo sanitize($a['id']); ?>">
                            <select name="status">
                                <option value="active" <?php echo $a['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $a['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <select name="is_available">
                                <option value="1" <?php echo (int)$a['is_available'] === 1 ? 'selected' : ''; ?>>Available</option>
                                <option value="0" <?php echo (int)$a['is_available'] === 0 ? 'selected' : ''; ?>>Not available</option>
                            </select>
                            <button type="submit" class="button">Save</button>
                        </form>
                        <form method="post" action="index.php?route=admin&action=delivery_agents" style="display:inline; margin-left:8px;">
                            <input type="hidden" name="reject_id" value="<?php echo sanitize($a['id']); ?>">
                            <button type="submit" class="button button-danger" onclick="return confirm('Reject and delete this agent? This will remove this account.')">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
