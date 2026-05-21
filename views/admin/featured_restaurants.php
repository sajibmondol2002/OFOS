<section class="section-title">
    <h2>Featured Restaurants</h2>
</section>
<div class="card"><div class="card-body">
    <h3>Current Featured</h3>
    <?php if (empty($featured)): ?>
        <p>No featured restaurants set.</p>
    <?php else: ?>
        <form method="post" action="index.php?route=admin&action=featured">
            <table class="table">
                <thead><tr><th>Priority</th><th>Name</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($featured as $f): ?>
                        <tr>
                            <td><input class="small-input" type="number" name="positions[<?php echo sanitize($f['id']); ?>]" value="<?php echo sanitize($f['priority']); ?>"></td>
                            <td><?php echo sanitize($f['name']); ?></td>
                            <td>
                                <button class="button button-danger" type="submit" name="remove_id" value="<?php echo sanitize($f['restaurant_id']); ?>">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="button" type="submit">Save Positions</button>
        </form>
    <?php endif; ?>
</div></div>
<div class="card" style="margin-top:16px;"><div class="card-body">
    <h3>Add Restaurant to Featured</h3>
    <form method="post" action="index.php?route=admin&action=featured">
        <select name="add_id">
            <?php foreach ($restaurants as $r): ?>
                <option value="<?php echo sanitize($r['id']); ?>"><?php echo sanitize($r['name']); ?></option>            <?php endforeach; ?>
        </select>
        <button class="button" type="submit">Add</button>
    </form>
</div></div>