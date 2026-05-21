<section class="section-title">
    <h2>Restaurant Profile</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" action="index.php?route=restaurant&action=profile" enctype="multipart/form-data">
            <div class="grid grid-2">
                <div class="input-group">
                    <label class="form-label" for="name">Restaurant Name</label>
                    <input id="name" type="text" name="name" value="<?php echo sanitize($restaurant['name'] ?? ''); ?>" >
                </div>
                <div class="input-group">
                    <label class="form-label" for="cuisine_type">Cuisine Type</label>
                    <input id="cuisine_type" type="text" name="cuisine_type" value="<?php echo sanitize($restaurant['cuisine_type'] ?? ''); ?>" >
                </div>
            </div>

            <div class="textarea-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?php echo sanitize($restaurant['description'] ?? ''); ?></textarea>
            </div>

            <div class="grid grid-2">
                <div class="input-group">
                    <label class="form-label" for="address">Address</label>
                    <input id="address" type="text" name="address" value="<?php echo sanitize($restaurant['address'] ?? ''); ?>" >
                </div>
                <div class="input-group">
                    <label class="form-label" for="city">City</label>
                    <input id="city" type="text" name="city" value="<?php echo sanitize($restaurant['city'] ?? ''); ?>" >
                </div>
            </div>

            <div class="grid grid-2">
                <div class="input-group">
                    <label class="form-label" for="opening_hours">Opening Hours</label>
                    <input id="opening_hours" type="text" name="opening_hours" value="<?php echo sanitize($restaurant['opening_hours'] ?? ''); ?>" placeholder="10:00 AM - 10:00 PM">
                </div>
                <div class="input-group">
                    <label class="form-label" for="delivery_radius_km">Delivery Radius (km)</label>
                    <input id="delivery_radius_km" type="number" min="0.1" step="0.1" name="delivery_radius_km" value="<?php echo sanitize($restaurant['delivery_radius_km'] ?? '5'); ?>" >
                </div>
            </div>

            <div class="input-group">
                <label class="form-label" for="logo">Logo</label>
                <input id="logo" type="file" name="logo" accept="image/*">
                <?php if (!empty($restaurant['logo_path'])): ?>
                    <p style="margin-top:8px;"><img src="../assets/images/<?php echo sanitize($restaurant['logo_path']); ?>" alt="<?php echo sanitize($restaurant['name']); ?>" style="height:72px; width:72px; object-fit:cover;"></p>
                <?php endif; ?>
            </div>

            <label style="display:flex; gap:8px; align-items:center; margin-bottom:18px;">
                <input type="checkbox" name="is_open" value="1" <?php echo (int) ($restaurant['is_open'] ?? 0) === 1 ? 'checked' : ''; ?>>
                Open for orders
            </label>

            <button class="button button-primary" type="submit">Save Profile</button>
        </form>
    </div>
</div>

