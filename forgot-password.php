<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$submitted = false;
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $email = trim((string) ($_POST['email'] ?? ''));
    $stmt = db()->prepare('SELECT id, name FROM users WHERE email = :email AND status = "active"');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        db()->prepare('DELETE FROM password_resets WHERE user_id = :uid')->execute(['uid' => $user['id']]);
        db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)')
            ->execute(['uid' => $user['id'], 'hash' => $tokenHash, 'exp' => $expiresAt]);

        // Glowelle has no outbound mail server configured in this environment, so the
        // reset link is surfaced directly here rather than emailed.
        $resetLink = base_url('reset-password.php?uid=' . $user['id'] . '&token=' . $token);
    }

    $submitted = true;
}

$pageTitle = 'Forgot Password — Glowelle';
$metaDescription = 'Reset your Glowelle account password.';
$canonicalPath = 'forgot-password.php';

require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1>Forgot Password</h1>
  <p class="text-center">Enter your email and we'll help you reset your password.</p>

  <?php if ($submitted): ?>
  <div class="flash flash-success" style="max-width:none;margin:0 0 24px;">
    If an account exists for that email, a password reset link has been generated.
  </div>
  <?php if ($resetLink): ?>
  <div class="summary-card" style="margin-bottom:24px;">
    <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:10px;">No email server is configured in this environment, so here is your reset link directly:</p>
    <a href="<?= e($resetLink) ?>" style="word-break:break-all;text-decoration:underline;"><?= e($resetLink) ?></a>
  </div>
  <?php endif; ?>
  <?php else: ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
  </form>
  <?php endif; ?>

  <p class="auth-links"><a href="<?= e(base_url('login.php')) ?>" style="text-decoration:underline;">Back to Login</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
