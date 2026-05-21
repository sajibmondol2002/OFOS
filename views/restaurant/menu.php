<section class="section-title">
    <h2>Menu Management</h2>
</section>
<?php require __DIR__ . '/nav.php'; ?>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="grid grid-2" style="margin-bottom:24px;">
    <div class="card">
        <div class="card-body">
            <h3>Add Category</h3>
            <form method="post" action="index.php?route=restaurant&action=menu">
                <input type="hidden" name="action" value="add_category">
                <div class="input-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" >
                </div>
                <div class="input-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" value="<?php echo count($categories) + 1; ?>" min="0">
                </div>
                <button type="submit" class="button button-primary">Create Category</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3>Add Menu Item</h3>
            <form method="post" action="index.php?route=restaurant&action=menu" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_item">
                <div class="input-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" >
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo sanitize($category['id']); ?>"><?php echo sanitize($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="name" >
                </div>
                <div class="textarea-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="input-group">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" step="0.01" min="0.01" >
                </div>
                <div class="input-group">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <label style="display:flex; gap:8px; align-items:center; margin-bottom:18px;">
                    <input type="checkbox" name="is_available" value="1" checked>
                    Available
                </label>
                <button type="submit" class="button button-primary">Add Item</button>
            </form>
        </div>
    </div>
</div>

<section class="section-title">
    <h2>Categories</h2>
</section>
<?php if (empty($categories)): ?>
    <div class="card"><div class="card-body"><p>No categories yet.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Name</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <form method="post" action="index.php?route=restaurant&action=menu">
                        <td>
                            <input type="hidden" name="action" value="update_category">
                            <input type="hidden" name="category_id" value="<?php echo sanitize($category['id']); ?>">
                            <input type="text" name="category_name" value="<?php echo sanitize($category['name']); ?>" >
                        </td>
                        <td><input type="number" name="display_order" value="<?php echo sanitize($category['display_order']); ?>" style="width:90px;"></td>
                        <td>
                            <button class="button" type="submit">Save</button>
                    </form>
                            <form method="post" action="index.php?route=restaurant&action=menu" style="display:inline;" onsubmit="return confirm('Delete this category and all items inside it?');">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?php echo sanitize($category['id']); ?>">
                                <button class="button button-danger" type="submit">Delete</button>
                            </form>
                        </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Menu Items</h2>
</section>
<?php if (empty($items)): ?>
    <div class="card"><div class="card-body"><p>No menu items yet.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Available</th><th>Active Discount</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo sanitize($item['name']); ?></strong>
                        <p style="margin:4px 0 0;"><?php echo sanitize($item['description']); ?></p>
                    </td>
                    <td><?php echo sanitize($item['category_name']); ?></td>
                    <td><?php echo formatCurrency((float) $item['price']); ?></td>
                    <td><?php echo (int) $item['is_available'] === 1 ? 'Yes' : 'No'; ?></td>
                    <td><?php echo (float) $item['active_discount_pct'] > 0 ? sanitize($item['active_discount_pct']) . '%' : '-'; ?></td>
                    <td>
                        <details>
                            <summary class="button">Edit</summary>
                            <form method="post" action="index.php?route=restaurant&action=menu" enctype="multipart/form-data" style="margin-top:12px; min-width:280px;">
                                <input type="hidden" name="action" value="update_item">
                                <input type="hidden" name="item_id" value="<?php echo sanitize($item['id']); ?>">
                                <div class="input-group">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" >
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo sanitize($category['id']); ?>" <?php echo (int) $category['id'] === (int) $item['category_id'] ? 'selected' : ''; ?>><?php echo sanitize($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" value="<?php echo sanitize($item['name']); ?>" >
                                </div>
                                <div class="textarea-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="3"><?php echo sanitize($item['description']); ?></textarea>
                                </div>
                                <div class="input-group">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" step="0.01" min="0.01" value="<?php echo sanitize($item['price']); ?>" >
                                </div>
                                <div class="input-group">
                                    <label class="form-label">Replace Image</label>
                                    <input type="file" name="image" accept="image/*">
                                </div>
                                <label style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
                                    <input type="checkbox" name="is_available" value="1" <?php echo (int) $item['is_available'] === 1 ? 'checked' : ''; ?>>
                                    Available
                                </label>
                                <button class="button button-primary" type="submit">Save Item</button>
                            </form>
                        </details>
                        <form method="post" action="index.php?route=restaurant&action=menu" style="display:inline;" onsubmit="return confirm('Delete this item?');">
                            <input type="hidden" name="action" value="delete_item">
                            <input type="hidden" name="item_id" value="<?php echo sanitize($item['id']); ?>">
                            <button class="button button-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<section class="section-title" style="margin-top:32px;">
    <h2>Discount Campaigns</h2>
</section>
<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <h3>Create Discount</h3>
        <form method="post" action="index.php?route=restaurant&action=menu">
            <input type="hidden" name="action" value="add_discount">
            <div class="grid grid-2">
                <div class="input-group">
                    <label class="form-label">Menu Item</label>
                    <select name="menu_item_id" >
                        <option value="">Select item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo sanitize($item['id']); ?>"><?php echo sanitize($item['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label class="form-label">Discount %</label>
                    <input type="number" name="discount_pct" min="1" max="99" step="0.01" >
                </div>
                <div class="input-group">
                    <label class="form-label">Valid From</label>
                    <input type="datetime-local" name="valid_from" >
                </div>
                <div class="input-group">
                    <label class="form-label">Valid Until</label>
                    <input type="datetime-local" name="valid_until" >
                </div>
            </div>
            <label style="display:flex; gap:8px; align-items:center; margin-bottom:18px;">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
            <button class="button button-primary" type="submit">Create Discount</button>
        </form>
    </div>
</div>

<?php if (empty($discounts)): ?>
    <div class="card"><div class="card-body"><p>No discount campaigns yet.</p></div></div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Item</th><th>Discount</th><th>Valid</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($discounts as $discount): ?>
                <tr>
                    <td><?php echo sanitize($discount['item_name']); ?></td>
                    <td><?php echo sanitize($discount['discount_pct']); ?>%</td>
                    <td><?php echo sanitize($discount['valid_from']); ?> to <?php echo sanitize($discount['valid_until']); ?></td>
                    <td><?php echo (int) $discount['is_active'] === 1 ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <form method="post" action="index.php?route=restaurant&action=menu" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_discount">
                            <input type="hidden" name="discount_id" value="<?php echo sanitize($discount['id']); ?>">
                            <input type="hidden" name="is_active" value="<?php echo (int) $discount['is_active'] === 1 ? '0' : '1'; ?>">
                            <button class="button" type="submit"><?php echo (int) $discount['is_active'] === 1 ? 'Deactivate' : 'Activate'; ?></button>
                        </form>
                        <form method="post" action="index.php?route=restaurant&action=menu" style="display:inline;" onsubmit="return confirm('Delete this discount?');">
                            <input type="hidden" name="action" value="delete_discount">
                            <input type="hidden" name="discount_id" value="<?php echo sanitize($discount['id']); ?>">
                            <button class="button button-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

