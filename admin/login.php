<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

if (is_admin()) {
    redirect('admin/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email AND role = "admin"');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $errors[] = 'Incorrect email or password.';
    } elseif ($user['status'] === 'disabled') {
        $errors[] = 'This admin account has been disabled.';
    } else {
        login_user($user);
        redirect('admin/index.php');
    }
}

$pageTitle = 'Admin Login';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — MADIXX</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Jost:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
</head>
<body>
<div class="admin-login-wrap">
  <div class="admin-login-card">
    <span class="logo">MADIXX</span>
    <p class="tag">Admin Panel</p>

    <?php if ($errors): ?>
    <div class="flash flash-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>
  </div>
</div>
</body>
</html>
