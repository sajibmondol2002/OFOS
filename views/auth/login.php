<section class="section-title">
    <h2>Login</h2>
</section>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="form-card" style="max-width:480px; margin:auto;">
    <form method="post" action="index.php?route=auth">
        <div class="input-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" >
        </div>
        <div class="input-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" >
        </div>
        <button type="submit" class="button button-primary">Login</button>
    </form>
    <p style="margin-top:18px;">Don't have an account? <a href="index.php?route=auth&action=unified">Register now</a>.</p>
</div>
