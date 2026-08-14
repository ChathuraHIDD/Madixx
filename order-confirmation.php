<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$orderNumber = trim((string) ($_GET['order'] ?? ''));

$stmt = db()->prepare('SELECT * FROM orders WHERE order_number = :num');
$stmt->execute(['num' => $orderNumber]);
$order = $stmt->fetch();

$forbidden = $order && $order['user_id'] !== null
    && (!is_logged_in() || ((int) $order['user_id'] !== current_user_id() && !is_admin()));

if (!$order || $forbidden) {
    http_response_code(404);
    $pageTitle = 'Order Not Found — MADIXX';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><i class="fa-regular fa-face-frown"></i><h2>Order not found</h2><p>We could not find that order.</p><a href="' . e(base_url('index.php')) . '" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemsStmt->execute(['id' => $order['id']]);
$orderItems = $itemsStmt->fetchAll();

$pageTitle = 'Order Confirmed — MADIXX';
$metaDescription = 'Your MADIXX order has been placed successfully.';
$canonicalPath = 'order-confirmation.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight" style="max-width:760px;">
  <div class="text-center" style="margin-bottom:40px;">
    <i class="fa-solid fa-circle-check" style="font-size:2.6rem;color:var(--success);margin-bottom:16px;display:block;"></i>
    <h1 style="font-size:2rem;">Thank you, <?= e($order['customer_name']) ?>!</h1>
    <p style="color:var(--text-muted);">Your order has been placed successfully.</p>
    <p style="font-family:var(--font-heading);font-size:1.4rem;margin-top:8px;">Order #<?= e($order['order_number']) ?></p>
  </div>

  <div class="summary-card" style="margin-bottom:24px;">
    <h3 style="margin-bottom:16px;">Order Items</h3>
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

  <div class="form-row" style="margin-bottom:32px;">
    <div>
      <h4 style="font-size:0.78rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-light);margin-bottom:10px;">Delivery Address</h4>
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

  <?php if ($order['payment_method'] === 'bank_transfer'): ?>
  <div class="flash flash-success" style="margin:0 0 32px;max-width:none;">
    <strong>Bank Transfer Details:</strong> MADIXX Beauty Ltd · Account #0123-4567-8901 · Please use your order number as the payment reference. Your order will be confirmed once payment is received.
  </div>
  <?php endif; ?>

  <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
    <a href="<?= e(base_url('order-tracking.php?order=' . urlencode($order['order_number']))) ?>" class="btn btn-primary">Track My Order</a>
    <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-outline">Continue Shopping</a>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
