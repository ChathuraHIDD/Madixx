<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$orderId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('admin/orders.php');
}

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $newStatus = (string) ($_POST['order_status'] ?? '');
    $newPaymentStatus = (string) ($_POST['payment_status'] ?? '');

    if (in_array($newStatus, $statuses, true) && in_array($newPaymentStatus, ['unpaid', 'paid'], true)) {
        $pdo->prepare('UPDATE orders SET order_status = :status, payment_status = :pstatus WHERE id = :id')
            ->execute(['status' => $newStatus, 'pstatus' => $newPaymentStatus, 'id' => $orderId]);
        flash('success', 'Order updated.');
        redirect('admin/order-details.php?id=' . $orderId);
    }
}

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemsStmt->execute(['id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$customer = null;
if ($order['user_id']) {
    $custStmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $custStmt->execute(['id' => $order['user_id']]);
    $customer = $custStmt->fetch();
}

$pageTitle = 'Order #' . $order['order_number'];
$activeAdminPage = 'orders';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="chart-grid" style="grid-template-columns:2fr 1fr;align-items:start;">
  <div class="admin-card">
    <div class="admin-card-head"><h3 style="margin:0;">Order Items</h3></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($orderItems as $item): ?>
          <tr>
            <td><?= e($item['product_name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td><?= format_price($item['price']) ?></td>
            <td><?= format_price($item['subtotal']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="max-width:280px;margin-left:auto;margin-top:16px;">
      <div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Subtotal</span><strong><?= format_price($order['subtotal']) ?></strong></div>
      <div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Shipping</span><strong><?= format_price($order['shipping']) ?></strong></div>
      <div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Discount</span><strong>-<?= format_price($order['discount']) ?></strong></div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--border-soft);font-weight:600;"><span>Total</span><strong><?= format_price($order['total']) ?></strong></div>
    </div>
  </div>

  <div>
    <div class="admin-card">
      <h3>Update Status</h3>
      <form method="post">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Order Status</label>
          <select name="order_status">
            <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Payment Status</label>
          <select name="payment_status">
            <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Order</button>
      </form>
    </div>

    <div class="admin-card">
      <h3>Customer</h3>
      <p><?= e($order['customer_name']) ?><br><?= e($order['customer_email']) ?><br><?= e($order['customer_phone']) ?></p>
      <?php if ($customer): ?>
      <a href="<?= e(base_url('admin/customer-details.php?id=' . (int) $customer['id'])) ?>" class="btn btn-outline btn-sm">View Customer</a>
      <?php else: ?>
      <p style="color:var(--text-light);font-size:0.82rem;">Guest checkout</p>
      <?php endif; ?>
    </div>

    <div class="admin-card">
      <h3>Shipping</h3>
      <p><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?><?= $order['shipping_province'] ? ', ' . e($order['shipping_province']) : '' ?><br><?= e($order['shipping_postal_code']) ?> <?= e($order['shipping_country']) ?></p>
      <p style="margin-top:12px;color:var(--text-muted);font-size:0.85rem;"><?= $order['delivery_method'] === 'express' ? 'Express Delivery' : 'Standard Delivery' ?></p>
    </div>

    <div class="admin-card">
      <h3>Payment</h3>
      <p><?= $order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery' ?></p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
