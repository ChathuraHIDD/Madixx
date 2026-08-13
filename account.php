<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$uid = current_user_id();

$pdo = db();

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = :uid');
$countStmt->execute(['uid' => $uid]);
$totalOrders = (int) $countStmt->fetchColumn();

$pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid AND order_status IN ('pending','confirmed','processing','shipped')");
$pendingStmt->execute(['uid' => $uid]);
$pendingOrders = (int) $pendingStmt->fetchColumn();

$completedStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid AND order_status = 'delivered'");
$completedStmt->execute(['uid' => $uid]);
$completedOrders = (int) $completedStmt->fetchColumn();

$wishlistStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = :uid');
$wishlistStmt->execute(['uid' => $uid]);
$wishlistCount = (int) $wishlistStmt->fetchColumn();

$recentStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5');
$recentStmt->execute(['uid' => $uid]);
$recentOrders = $recentStmt->fetchAll();

$activeAccountPage = 'dashboard';
$pageTitle = 'My Account — Glowelle';
$canonicalPath = 'account.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'My Account', 'url' => null]]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <h1 style="font-size:1.9rem;margin-bottom:8px;">Welcome, <?= e($user['name']) ?></h1>
      <p style="color:var(--text-muted);margin-bottom:32px;">Here's an overview of your Glowelle account.</p>

      <div class="stat-grid">
        <div class="stat-card"><div class="stat-num"><?= $totalOrders ?></div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-num"><?= $pendingOrders ?></div><div class="stat-label">Pending Orders</div></div>
        <div class="stat-card"><div class="stat-num"><?= $completedOrders ?></div><div class="stat-label">Completed Orders</div></div>
        <div class="stat-card"><div class="stat-num"><?= $wishlistCount ?></div><div class="stat-label">Wishlist Items</div></div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="margin:0;">Recent Orders</h3>
        <a href="<?= e(base_url('orders.php')) ?>" style="font-size:0.85rem;text-decoration:underline;">View All</a>
      </div>

      <?php if ($recentOrders): ?>
      <?php foreach ($recentOrders as $order): ?>
      <div class="order-row">
        <div>
          <strong>#<?= e($order['order_number']) ?></strong>
          <div style="font-size:0.82rem;color:var(--text-muted);"><?= e(date('M j, Y', strtotime($order['created_at']))) ?> · <?= format_price($order['total']) ?></div>
        </div>
        <span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span>
        <a href="<?= e(base_url('order-details.php?id=' . (int) $order['id'])) ?>" class="btn btn-outline btn-sm">View</a>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <p style="color:var(--text-muted);">You haven't placed any orders yet. <a href="<?= e(base_url('shop.php')) ?>" style="text-decoration:underline;">Start shopping</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
