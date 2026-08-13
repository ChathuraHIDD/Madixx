<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$uid = current_user_id();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $stmt = db()->prepare('SELECT password FROM users WHERE id = :id');
    $stmt->execute(['id' => $uid]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($currentPassword, $row['password'])) {
        $errors[] = 'Your current password is incorrect.';
    }
    if (strlen($newPassword) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($newPassword !== $confirmPassword) $errors[] = 'New passwords do not match.';

    if (!$errors) {
        db()->prepare('UPDATE users SET password = :password WHERE id = :id')
            ->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $uid]);
        $success = true;
    }
}

$activeAccountPage = 'password';
$pageTitle = 'Change Password — Glowelle';
$canonicalPath = 'account-password.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'My Account', 'url' => base_url('account.php')], ['label' => 'Change Password', 'url' => null]]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:1.9rem;margin-bottom:24px;">Change Password</h1>

      <?php if ($success): ?>
      <div class="flash flash-success" style="max-width:none;margin:0 0 24px;">Your password has been updated.</div>
      <?php endif; ?>
      <?php if ($errors): ?>
      <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
      <?php endif; ?>

      <form method="post" style="max-width:520px;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Current Password</label>
          <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
          <label>New Password</label>
          <input type="password" name="new_password" required minlength="8">
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" name="confirm_password" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
