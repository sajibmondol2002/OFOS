<section class="section-title">
    <h2>Customer Complaints</h2>
</section>
<?php if (empty($complaints)): ?>

    <div class="card"><div class="card-body"><p>No complaints found.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>ID</th><th>Customer</th><th>Subject</th><th>Status</th><th>Placed</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($complaints as $c): ?>
                <tr>
                    <td><?php echo sanitize($c['id']); ?></td>
                    <td><?php echo sanitize($c['customer']); ?></td>
                    <td><?php echo sanitize($c['subject']); ?></td>
                    <td><?php echo sanitize($c['status']); ?></td>
                    <td><?php echo sanitize($c['created_at']); ?></td>
                    <td><a class="button" href="index.php?route=admin&action=view_complaint&id=<?php echo sanitize($c['id']); ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
