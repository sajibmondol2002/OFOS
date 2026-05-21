<section class="section-title">
    <h2>Manage Products</h2>
</section>

<?php if ($message): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo sanitize($error); ?></div><?php endif; ?>

<div class="grid grid-2">
    <div class="form-card">
        <h3>Add Menu Item</h3>
        <form method="post" action="index.php?route=admin&action=products">
            <div class="input-group">
                <label class="form-label" for="name">Name</label>
                <input id="name" type="text" name="name" >
            </div>
            <div class="textarea-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            <div class="input-group">
                <label class="form-label" for="price">Price</label>
                <input id="price" type="number" name="price" step="0.01" min="0.01" >
            </div>
            <div class="input-group">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" >
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo sanitize($category['id']); ?>"><?php echo sanitize($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label class="form-label" for="image">Image filename</label>
                <input id="image" type="text" name="image" value="placeholder.png">
            </div>
            <div class="input-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="button button-primary">Create Product</button>
        </form>
    </div>

    <div>
        <h3>Menu Items</h3>
        <?php if (empty($products)): ?>
            <div class="card"><div class="card-body"><p>No products found.</p></div></div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo sanitize($product['name']); ?></td>
                            <td><?php echo sanitize($product['category_name']); ?></td>
                            <td><?php echo formatCurrency((float)$product['price']); ?></td>
                            <td><?php echo sanitize($product['status']); ?></td>
                            <td>
                                <a href="index.php?route=admin&action=products&delete=<?php echo (int)$product['id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this item?')"
                                   style="color: red; font-weight: bold;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>