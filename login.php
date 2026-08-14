<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('account.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $errors[] = 'Incorrect email or password.';
    } elseif ($user['status'] === 'disabled') {
        $errors[] = 'This account has been disabled. Please contact support.';
    } else {
        login_user($user);
        flash('success', 'Welcome back, ' . $user['name'] . '!');

        $redirectTo = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);

        if ($user['role'] === 'admin') {
            redirect('admin/index.php');
        }
        redirect($redirectTo ? ltrim(str_replace(BASE_URL, '', $redirectTo), '/') : 'account.php');
    }
}

$pageTitle = 'Login — MADIXX';
$metaDescription = 'Log in to your MADIXX account.';
$canonicalPath = 'login.php';

require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1>Welcome Back</h1>
  <p class="text-center">Log in to continue shopping with MADIXX.</p>

  <?php if ($errors): ?>
  <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required value="<?= e($email) ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
      <a href="<?= e(base_url('forgot-password.php')) ?>" style="font-size:0.85rem;color:var(--text-muted);text-decoration:underline;">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Log In</button>
  </form>

  <p class="auth-links">New to MADIXX? <a href="<?= e(base_url('register.php')) ?>" style="text-decoration:underline;">Create an account</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
