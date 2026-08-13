<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$custId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id AND role = "customer"');
$stmt->execute(['id' => $custId]);
$customer = $stmt->fetch();

if (!$customer) {
    flash('error', 'Customer not found.');
    redirect('admin/customers.php');
}

$ordersStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :id ORDER BY created_at DESC');
$ordersStmt->execute(['id' => $custId]);
$orders = $ordersStmt->fetchAll();

$totalSpent = array_sum(array_column($orders, 'total'));

$pageTitle = $customer['name'];
$activeAdminPage = 'customers';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="chart-grid" style="grid-template-columns:1fr 2fr;align-items:start;">
  <div class="admin-card">
    <h3>Customer Info</h3>
    <p><strong><?= e($customer['name']) ?></strong></p>
    <p style="color:var(--text-muted);"><?= e($customer['email']) ?><br><?= e((string) $customer['phone']) ?></p>
    <p style="margin-top:12px;"><span class="status-pill status-<?= e($customer['status']) ?>"><?= e(ucfirst($customer['status'])) ?></span></p>
    <p style="margin-top:12px;color:var(--text-light);font-size:0.82rem;">Joined <?= e(date('M j, Y', strtotime($customer['created_at']))) ?></p>
    <div class="admin-stat-grid" style="grid-template-columns:1fr;margin-top:20px;">
      <div class="admin-stat-card"><div class="num"><?= count($orders) ?></div><div class="label">Total Orders</div></div>
      <div class="admin-stat-card"><div class="num"><?= format_price($totalSpent) ?></div><div class="label">Total Spent</div></div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-head"><h3 style="margin:0;">Order History</h3></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td>#<?= e($order['order_number']) ?></td>
            <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
            <td><?= format_price($order['total']) ?></td>
            <td><span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
            <td><a href="<?= e(base_url('admin/order-details.php?id=' . (int) $order['id'])) ?>" class="btn btn-outline btn-sm">View</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$orders): ?><tr><td colspan="5" class="empty-row">No orders yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
