<section class="section-title">
    <h2>Featured Restaurants</h2>
</section>
<form method="post" action="index.php?route=admin&action=featured" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <label for="restaurant_id">Select Restaurant</label>
    <select id="restaurant_id" name="restaurant_id" >
        <?php foreach ($restaurants as $r): ?>
            <option value="<?php echo sanitize($r['id']); ?>" <?php echo isset($featuredId) && $featuredId == $r['id'] ? 'selected' : ''; ?>><?php echo sanitize($r['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <label for="is_featured">Featured</label>
    <select id="is_featured" name="is_featured">
        <option value="1" <?php echo $isFeatured ? 'selected' : ''; ?>>Yes</option>
        <option value="0" <?php echo !$isFeatured ? 'selected' : ''; ?>>No</option>
    </select>
    <button type="submit" class="button">Update</button>
</form>
<div class="card">
    <div class="card-body">
        <h3>Current Featured Restaurants</h3>
        <?php if (empty($featuredRestaurants)): ?>
            <p>No restaurants are currently featured.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($featuredRestaurants as $restaurant): ?>
                    <li><?php echo sanitize($restaurant['name']); ?> <?php echo $restaurant['is_featured'] ? '(Featured)' : ''; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
