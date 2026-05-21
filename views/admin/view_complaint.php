<section class="section-title">
    <h2>Complaint Details</h2>
</section>
<div class="card">
    <div class="card-body">
        <h3><?php echo sanitize($complaint['subject']); ?></h3>
        <p><strong>Customer:</strong> <?php echo sanitize($complaint['customer']); ?> &lt;<?php echo sanitize($complaint['customer_email']); ?>&gt;</p>
        <p><strong>Status:</strong> <?php echo sanitize($complaint['status']); ?></p>
        <p><strong>Placed:</strong> <?php echo sanitize($complaint['created_at']); ?></p>
        <p><strong>Message:</strong></p>
        <p><?php echo nl2br(sanitize($complaint['message'])); ?></p>
        <form method="post" action="index.php?route=admin&action=view_complaint&id=<?php echo sanitize($complaint['id']); ?>" style="margin-top:16px;">
            <div class="input-group">
                <label for="status">Update status</label>
                <select id="status" name="status">
                    <option value="open" <?php echo $complaint['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            <div class="input-group">
                <label for="admin_note">Admin note</label>
                <textarea id="admin_note" name="admin_note" rows="4"><?php echo sanitize($complaint['admin_note'] ?? ''); ?></textarea>
            </div>
            <button class="button button-primary" type="submit">Save</button>
        </form>
    </div>
</div>
