<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id AND user_id = :uid');
$stmt->execute(['id' => $orderId, 'uid' => current_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemsStmt->execute(['id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentIndex = array_search($order['order_status'], $statuses, true);

$activeAccountPage = 'orders';
$pageTitle = 'Order #' . $order['order_number'] . ' — Glowelle';
$canonicalPath = 'order-details.php?id=' . $orderId;

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([
      ['label' => 'My Account', 'url' => base_url('account.php')],
      ['label' => 'My Orders', 'url' => base_url('orders.php')],
      ['label' => '#' . $order['order_number'], 'url' => null],
  ]) ?>

  <div class="account-layout">
    <?php require __DIR__ . '/includes/account-sidebar.php'; ?>

    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:10px;">
        <h1 style="font-size:1.9rem;margin:0;">Order #<?= e($order['order_number']) ?></h1>
        <span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span>
      </div>
      <p style="color:var(--text-muted);margin-bottom:32px;">Placed on <?= e(date('F j, Y', strtotime($order['created_at']))) ?></p>

      <?php if ($order['order_status'] !== 'cancelled'): ?>
      <div class="track-progress">
        <?php foreach ($statuses as $i => $status): ?>
        <div class="track-step<?= $i <= $currentIndex ? ' done' : '' ?>">
          <div class="dot"><i class="fa-solid fa-check"></i></div>
          <span><?= e(ucfirst($status)) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="summary-card" style="margin-bottom:24px;">
        <h3 style="margin-bottom:16px;">Items</h3>
        <?php foreach ($orderItems as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-soft);">
          <span><?= e($item['product_name']) ?> &times; <?= (int) $item['quantity'] ?></span>
          <span><?= format_price($item['subtotal']) ?></span>
        </div>
        <?php endforeach; ?>
        <div class="summary-row" style="margin-top:16px;"><span>Subtotal</span><strong><?= format_price($order['subtotal']) ?></strong></div>
        <div class="summary-row"><span>Shipping</span><strong><?= $order['shipping'] > 0 ? format_price($order['shipping']) : 'Free' ?></strong></div>
        <div class="summary-row"><span>Discount</span><strong>-<?= format_price($order['discount']) ?></strong></div>
        <div class="summary-row total"><span>Total</span><strong><?= format_price($order['total']) ?></strong></div>
      </div>

      <div class="form-row">
        <div>
          <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:10px;">Shipping Address</h4>
          <p style="color:var(--text-muted);"><?= e($order['shipping_address']) ?>, <?= e($order['shipping_city']) ?><?= $order['shipping_province'] ? ', ' . e($order['shipping_province']) : '' ?><br><?= e($order['shipping_country']) ?></p>
        </div>
        <div>
          <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:10px;">Payment &amp; Delivery</h4>
          <p style="color:var(--text-muted);">
            <?= $order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery' ?><br>
            <?= $order['delivery_method'] === 'express' ? 'Express Delivery' : 'Standard Delivery' ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
