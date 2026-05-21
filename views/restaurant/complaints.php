<section class="section-title">
    <h2>Restaurant Complaints</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if (empty($complaints)): ?>
    <div class="card"><div class="card-body"><p>No complaints are linked to this restaurant.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>ID</th><th>Submitter</th><th>Subject</th><th>Description</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
            <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td>#<?php echo sanitize($complaint['id']); ?></td>
                    <td><?php echo sanitize($complaint['submitter']); ?></td>
                    <td><?php echo sanitize($complaint['subject']); ?></td>
                    <td><?php echo sanitize($complaint['description']); ?></td>
                    <td><?php echo sanitize($complaint['status']); ?></td>
                    <td><?php echo sanitize($complaint['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

