<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Food Ordering System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>
    <main class="container">
        <?php if (!empty($isAdminView)): ?>
            <?php require __DIR__ . '/admin/nav.php'; ?>
        <?php endif; ?>
        <?php echo isset($content) ? $content : ''; ?>
    </main>
    <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
