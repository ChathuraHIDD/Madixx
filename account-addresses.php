<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$uid = current_user_id();
$pdo = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = (string) ($_POST['action'] ?? 'add');

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM addresses WHERE id = :id AND user_id = :uid')
            ->execute(['id' => (int) $_POST['address_id'], 'uid' => $uid]);
        flash('success', 'Address removed.');
        redirect('account-addresses.php');
    }

    if ($action === 'set_default') {
        $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = :uid')->execute(['uid' => $uid]);
        $pdo->prepare('UPDATE addresses SET is_default = 1 WHERE id = :id AND user_id = :uid')
            ->execute(['id' => (int) $_POST['address_id'], 'uid' => $uid]);
        flash('success', 'Default address updated.');
        redirect('account-addresses.php');
    }

    if ($action === 'add') {
        $label = trim((string) ($_POST['label'] ?? 'Home'));
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $addressLine = trim((string) ($_POST['address_line'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $province = trim((string) ($_POST['province'] ?? ''));
        $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
        $country = trim((string) ($_POST['country'] ?? 'Sri Lanka'));

        if ($fullName === '') $errors[] = 'Full name is required.';
        if ($addressLine === '') $errors[] = 'Address is required.';
        if ($city === '') $errors[] = 'City is required.';

        if (!$errors) {
            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM addresses WHERE user_id = :uid');
            $countStmt->execute(['uid' => $uid]);
            $isFirst = (int) $countStmt->fetchColumn() === 0;

            $pdo->prepare(
                'INSERT INTO addresses (user_id, label, full_name, phone, address_line, city, province, postal_code, country, is_default)
                 VALUES (:uid, :label, :full_name, :phone, :address_line, :city, :province, :postal_code, :country, :is_default)'
            )->execute([
                'uid' => $uid, 'label' => $label, 'full_name' => $fullName, 'phone' => $phone,
                'address_line' => $addressLine, 'city' => $city, 'province' => $province,
                'postal_code' => $postalCode, 'country' => $country, 'is_default' => $isFirst ? 1 : 0,
            ]);
            flash('success', 'Address added.');
            redirect('account-addresses.php');
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, created_at DESC');
$stmt->execute(['uid' => $uid]);
$addresses = $stmt->fetchAll();

$activeAccountPage = 'addresses';
$pageTitle = 'My Addresses — Glowelle';
$canonicalPath = 'account-addresses.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'My Account', 'url' => base_url('account.php')], ['label' => 'Addresses', 'url' => null]]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:1.9rem;margin-bottom:24px;">My Addresses</h1>

      <?php if ($errors): ?>
      <div class="flash flash-error" style="max-width:none;margin:0 0 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
      <?php endif; ?>

      <?php foreach ($addresses as $addr): ?>
      <div class="order-row" style="align-items:flex-start;">
        <div>
          <strong><?= e($addr['label']) ?></strong> <?php if ($addr['is_default']): ?><span class="status-pill status-delivered">Default</span><?php endif; ?>
          <p style="color:var(--text-muted);margin:6px 0 0;">
            <?= e($addr['full_name']) ?><br>
            <?= e($addr['address_line']) ?>, <?= e($addr['city']) ?><?= $addr['province'] ? ', ' . e($addr['province']) : '' ?><br>
            <?= e($addr['postal_code']) ?> <?= e($addr['country']) ?><br>
            <?= e((string) $addr['phone']) ?>
          </p>
        </div>
        <div style="display:flex;gap:8px;">
          <?php if (!$addr['is_default']): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="set_default"><input type="hidden" name="address_id" value="<?= (int) $addr['id'] ?>">
            <button type="submit" class="btn btn-outline btn-sm">Set Default</button>
          </form>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('Remove this address?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="address_id" value="<?= (int) $addr['id'] ?>">
            <button type="submit" class="btn btn-outline btn-sm">Remove</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="summary-card" style="margin-top:32px;max-width:560px;">
        <h3 style="margin-bottom:20px;">Add New Address</h3>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add">
          <div class="form-row">
            <div class="form-group"><label>Label</label><input type="text" name="label" placeholder="Home, Office…" value="Home"></div>
            <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
          </div>
          <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
          <div class="form-group"><label>Street Address</label><input type="text" name="address_line" required></div>
          <div class="form-row">
            <div class="form-group"><label>City</label><input type="text" name="city" required></div>
            <div class="form-group"><label>Province</label><input type="text" name="province"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Postal Code</label><input type="text" name="postal_code"></div>
            <div class="form-group"><label>Country</label><input type="text" name="country" value="Sri Lanka"></div>
          </div>
          <button type="submit" class="btn btn-primary">Save Address</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
