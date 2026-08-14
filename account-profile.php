<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$uid = current_user_id();
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));

    if ($name === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($phone === '') $errors[] = 'Please enter your phone number.';

    if (!$errors) {
        $dupStmt = db()->prepare('SELECT id FROM users WHERE email = :email AND id != :uid');
        $dupStmt->execute(['email' => $email, 'uid' => $uid]);
        if ($dupStmt->fetch()) {
            $errors[] = 'That email address is already in use by another account.';
        }
    }

    if (!$errors) {
        db()->prepare('UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id')
            ->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'id' => $uid]);
        $_SESSION['user_name'] = $name;
        flash('success', 'Your profile has been updated.');
        redirect('account-profile.php');
    }

    $user = ['name' => $name, 'email' => $email, 'phone' => $phone] + $user;
}

$activeAccountPage = 'profile';
$pageTitle = 'My Profile — MADIXX';
$canonicalPath = 'account-profile.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'My Account', 'url' => base_url('account.php')], ['label' => 'My Profile', 'url' => null]]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:1.9rem;margin-bottom:24px;">My Profile</h1>

      <?php if ($errors): ?>
      <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
      <?php endif; ?>

      <form method="post" style="max-width:520px;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" required value="<?= e($user['name']) ?>">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" required value="<?= e($user['email']) ?>">
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" required value="<?= e((string) ($user['phone'] ?? '')) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
