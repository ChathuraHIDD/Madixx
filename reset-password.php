<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$uid = (int) ($_GET['uid'] ?? $_POST['uid'] ?? 0);
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$success = false;

function find_valid_reset(int $uid, string $token): ?array
{
    if ($uid < 1 || $token === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM password_resets WHERE user_id = :uid AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute(['uid' => $uid]);
    $reset = $stmt->fetch();

    if (!$reset || !hash_equals($reset['token_hash'], hash('sha256', $token))) {
        return null;
    }

    return $reset;
}

$reset = find_valid_reset($uid, $token);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    csrf_require();

    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        db()->prepare('UPDATE users SET password = :password WHERE id = :id')
            ->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $uid]);
        db()->prepare('DELETE FROM password_resets WHERE user_id = :uid')->execute(['uid' => $uid]);
        $success = true;
    }
}

$pageTitle = 'Reset Password — Glowelle';
$canonicalPath = 'reset-password.php';

require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1>Reset Password</h1>

  <?php if (!$reset && !$success): ?>
  <div class="empty-state">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <h2>Link Expired or Invalid</h2>
    <p>This password reset link is invalid or has expired. Please request a new one.</p>
    <a href="<?= e(base_url('forgot-password.php')) ?>" class="btn btn-primary">Request New Link</a>
  </div>
  <?php elseif ($success): ?>
  <div class="flash flash-success" style="max-width:none;margin:0 0 24px;">Your password has been reset successfully.</div>
  <p class="text-center"><a href="<?= e(base_url('login.php')) ?>" class="btn btn-primary">Log In Now</a></p>
  <?php else: ?>
  <p class="text-center">Choose a new password for your account.</p>

  <?php if ($errors): ?>
  <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="uid" value="<?= (int) $uid ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" required minlength="8">
    </div>
    <div class="form-group">
      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" required minlength="8">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
  </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
