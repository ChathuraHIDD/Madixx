<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

$search = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(order_number LIKE :s1 OR customer_name LIKE :s2 OR customer_email LIKE :s3)';
    $needle = '%' . $search . '%';
    $params['s1'] = $needle;
    $params['s2'] = $needle;
    $params['s3'] = $needle;
}
if ($status !== '') {
    $where[] = 'order_status = :status';
    $params['status'] = $status;
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $page] = paginate($total, $perPage, $page);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

$pageTitle = 'Orders';
$activeAdminPage = 'orders';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head"><h3 style="margin:0;"><?= (int) $total ?> Orders</h3></div>

  <form method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Search order #, name or email…" value="<?= e($search) ?>">
    <select name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <?php foreach ($statuses as $s): ?>
      <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td><strong>#<?= e($order['order_number']) ?></strong></td>
          <td><?= e($order['customer_name']) ?><br><small style="color:var(--text-light);"><?= e($order['customer_email']) ?></small></td>
          <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
          <td><?= $order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : 'COD' ?></td>
          <td><?= format_price($order['total']) ?></td>
          <td><span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
          <td><a href="<?= e(base_url('admin/order-details.php?id=' . (int) $order['id'])) ?>" class="btn btn-outline btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="7" class="empty-row">No orders found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?= render_pagination($page, $totalPages) ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
