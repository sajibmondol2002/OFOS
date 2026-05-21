<section class="section-title">
    <h2>Register</h2>
</section>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="form-card" style="max-width:480px; margin:auto;">
    <form method="post" action="index.php?route=auth&action=register">
        <div class="input-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo sanitize($_POST['name'] ?? ''); ?>" required>
        </div>
        <div class="input-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="input-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="input-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="button button-primary">Create Account</button>
    </form>
    <p style="margin-top:18px;">Already have an account? <a href="index.php?route=auth&action=unified">Login here</a>.</p>
</div>
