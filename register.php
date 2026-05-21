<?php
require_once __DIR__ . '/inc/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $existing = db_fetch_one('SELECT id FROM users WHERE email = ?', 's', [$email]);
        if ($existing) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db_execute('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)', 'sssss', [$name, $email, $hash, 'customer', date('Y-m-d H:i:s')]);
            $userId = db_insert_id();
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
            ];
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Food Ordering</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Online Food Ordering</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart (<?php echo cartCount(); ?>)</a>
            <a href="login.php">Login</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Register</h2>
    </section>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:480px; margin:auto;">
        <form method="post" action="register.php">
            <div class="input-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo sanitize($_POST['name'] ?? ''); ?>" >
            </div>
            <div class="input-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" >
            </div>
            <div class="input-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" >
            </div>
            <div class="input-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" >
            </div>
            <button type="submit" class="button button-primary">Create Account</button>
        </form>
        <p style="margin-top:18px;">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
