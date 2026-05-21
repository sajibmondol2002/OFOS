<section class="section-title">
    <h2>Customer Reviews</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<?php if (empty($reviews)): ?>
    <div class="card"><div class="card-body"><p>No reviews have been submitted for this restaurant yet.</p></div></div>
<?php else: ?>
    <div class="grid grid-2" style="gap:16px; margin-bottom:24px;">
        <?php foreach ($reviews as $review): ?>
            <div class="card">
                <div class="card-body">
                    <h3><?php echo sanitize($review['customer']); ?> <small>(<?php echo sanitize($review['rating']); ?>/5)</small></h3>
                    <p><?php echo sanitize($review['comment']); ?></p>
                    <p><strong>Order:</strong> #<?php echo sanitize($review['order_id']); ?> | <strong>Status:</strong> <?php echo sanitize($review['order_status']); ?></p>
                    <p><em>Posted: <?php echo sanitize($review['created_at']); ?></em></p>

                    <?php if (!empty($review['manager_reply'])): ?>
                        <div class="alert alert-success"><strong>Your reply:</strong> <?php echo sanitize($review['manager_reply']); ?></div>
                    <?php endif; ?>

                    <form method="post" action="index.php?route=restaurant&action=reviews">
                        <input type="hidden" name="review_id" value="<?php echo sanitize($review['id']); ?>">
                        <div class="textarea-group">
                            <label class="form-label">Public Reply</label>
                            <textarea name="manager_reply" rows="3" ><?php echo sanitize($review['manager_reply'] ?? ''); ?></textarea>
                        </div>
                        <button class="button button-primary" type="submit">Post Reply</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

