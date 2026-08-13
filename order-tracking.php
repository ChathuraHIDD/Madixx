<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$orderNumber = trim((string) ($_GET['order'] ?? ''));
$order = null;

if ($orderNumber !== '') {
    $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = :num');
    $stmt->execute(['num' => $orderNumber]);
    $order = $stmt->fetch() ?: null;
}

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentIndex = $order ? array_search($order['order_status'], $statuses, true) : false;

$pageTitle = 'Track Your Order — Glowelle';
$metaDescription = 'Track the status of your Glowelle order.';
$canonicalPath = 'order-tracking.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight" style="max-width:720px;">
  <div class="text-center" style="margin-bottom:32px;">
    <p class="eyebrow">Order Tracking</p>
    <h1 style="font-size:2rem;">Track Your Order</h1>
    <p style="color:var(--text-muted);">Enter your order number to see its current status.</p>
  </div>

  <form method="get" style="display:flex;gap:12px;max-width:480px;margin:0 auto 40px;">
    <input type="text" name="order" placeholder="e.g. GW10245" value="<?= e($orderNumber) ?>" required>
    <button type="submit" class="btn btn-primary">Track</button>
  </form>

  <?php if ($orderNumber !== '' && !$order): ?>
  <div class="empty-state">
    <i class="fa-regular fa-face-frown"></i>
    <h2>Order not found</h2>
    <p>Please double-check your order number and try again.</p>
  </div>
  <?php elseif ($order): ?>
  <div class="summary-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:10px;">
      <h3 style="margin:0;">Order #<?= e($order['order_number']) ?></h3>
      <span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span>
    </div>
    <p style="color:var(--text-muted);margin-bottom:0;">Placed on <?= e(date('F j, Y', strtotime($order['created_at']))) ?> · Total <?= format_price($order['total']) ?></p>

    <?php if ($order['order_status'] === 'cancelled'): ?>
    <div class="flash flash-error" style="max-width:none;margin:28px 0 0;">
      This order has been cancelled. Please contact us if you have any questions.
    </div>
    <?php else: ?>
    <div class="track-progress">
      <?php foreach ($statuses as $i => $status): ?>
      <div class="track-step<?= $i <= $currentIndex ? ' done' : '' ?>">
        <div class="dot"><i class="fa-solid fa-check"></i></div>
        <span><?= e(ucfirst($status)) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
