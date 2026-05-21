<section class="section-title">
    <h2>Manage Categories</h2>
</section>

<?php if ($message): ?><div class="alert alert-success"><?php echo sanitize($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo sanitize($error); ?></div><?php endif; ?>

<div class="grid grid-2">
    <div class="form-card">
        <h3>Add Category</h3>
        <form method="post" action="index.php?route=admin&action=categories">
            <div class="input-group">
                <label class="form-label" for="name">Name</label>
                <input id="name" type="text" name="name" >
            </div>
            <div class="textarea-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            <button type="submit" class="button button-primary">Save Category</button>
        </form>
    </div>

    <div>
        <h3>Existing Categories</h3>
        <?php if (empty($categories)): ?>
            <div class="card"><div class="card-body"><p>No categories found.</p></div></div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Name</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo sanitize($category['name']); ?></td>
                            <td><a href="index.php?route=admin&action=categories&delete=<?php echo sanitize($category['id']); ?>" onclick="return confirm('Delete this category?');">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
