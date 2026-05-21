<?php
require_once __DIR__ . '/../inc/functions.php';

if (isLoggedIn() && isAdmin()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $user = db_fetch_one('SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ?', 'ss', [$email, 'admin']);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            redirect('dashboard.php');
        }
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Online Food Ordering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="site-title">Admin Portal</div>
        <nav>
            <a href="../index.php">Customer Site</a>
        </nav>
    </div>
</header>
<main class="container">
    <section class="section-title">
        <h2>Admin Login</h2>
    </section>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:480px; margin:auto;">
        <form method="post" action="login.php">
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
    </div>
</main>
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Online Food Ordering System</p>
    </div>
</footer>
</body>
</html>
