<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($phone === '') $errors[] = 'Please enter your phone number.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $checkStmt = db()->prepare('SELECT id FROM users WHERE email = :email');
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (name, email, phone, password, role, status) VALUES (:name, :email, :phone, :password, "customer", "active")');
        $stmt->execute([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $userId = (int) db()->lastInsertId();
        login_user(['id' => $userId, 'name' => $name, 'role' => 'customer']);

        flash('success', 'Welcome to MADIXX, ' . $name . '!');
        redirect('account.php');
    }
}

$pageTitle = 'Create an Account — MADIXX';
$metaDescription = 'Create your MADIXX account to track orders, save your wishlist and check out faster.';
$canonicalPath = 'register.php';

require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1>Create an Account</h1>
  <p class="text-center">Join MADIXX for faster checkout, order tracking and wishlists.</p>

  <?php if ($errors): ?>
  <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" required value="<?= e($name) ?>">
    </div>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required value="<?= e($email) ?>">
    </div>
    <div class="form-group">
      <label>Phone Number</label>
      <input type="tel" name="phone" required value="<?= e($phone) ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required minlength="8">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required minlength="8">
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
  </form>

  <p class="auth-links">Already have an account? <a href="<?= e(base_url('login.php')) ?>" style="text-decoration:underline;">Log in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
