<section class="section-title">
    <h2>Platform Settings</h2>
</section>
<?php if (!empty($message)): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
<div class="card"><div class="card-body">
    <form method="post" action="index.php?route=admin&action=settings">
        <div class="input-group"><label>Commission rate (%)</label><input type="text" name="commission_rate" value="<?php echo sanitize($commission); ?>"></div>
        <div class="input-group"><label>Base delivery fee</label><input type="text" name="base_delivery_fee" value="<?php echo sanitize($base_fee); ?>"></div>
        <div class="input-group"><label>Per km fee</label><input type="text" name="per_km_fee" value="<?php echo sanitize($per_km); ?>"></div>
        <div class="input-group"><label>Estimated delivery time formula</label><input type="text" name="estimated_time_formula" value="<?php echo sanitize($formula); ?>"></div>
        <button class="button button-primary" type="submit">Save</button>
    </form>
</div></div>
