<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$uid = current_user_id();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$countStmt = db()->prepare('SELECT COUNT(*) FROM orders WHERE user_id = :uid');
$countStmt->execute(['uid' => $uid]);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $page] = paginate($total, $perPage, $page);

$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue('uid', $uid);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

$activeAccountPage = 'orders';
$pageTitle = 'My Orders — Glowelle';
$canonicalPath = 'orders.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'My Account', 'url' => base_url('account.php')], ['label' => 'My Orders', 'url' => null]]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:1.9rem;margin-bottom:24px;">My Orders</h1>

      <?php if ($orders): ?>
      <?php foreach ($orders as $order): ?>
      <div class="order-row">
        <div>
          <strong>#<?= e($order['order_number']) ?></strong>
          <div style="font-size:0.82rem;color:var(--text-muted);"><?= e(date('M j, Y', strtotime($order['created_at']))) ?> · <?= format_price($order['total']) ?></div>
        </div>
        <span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span>
        <a href="<?= e(base_url('order-details.php?id=' . (int) $order['id'])) ?>" class="btn btn-outline btn-sm">View Details</a>
      </div>
      <?php endforeach; ?>
      <?= render_pagination($page, $totalPages) ?>
      <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-box"></i>
        <h2>No orders yet</h2>
        <p>When you place an order, it will show up here.</p>
        <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Start Shopping</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
