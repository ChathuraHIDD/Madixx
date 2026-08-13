<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

$totalSales = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn();
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalCustomers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$lowStockProducts = $pdo->query('SELECT id, name, stock, sku FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 8')->fetchAll();

$salesByDay = $pdo->query(
    "SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS total
     FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND order_status != 'cancelled'
     GROUP BY DATE(created_at) ORDER BY d ASC"
)->fetchAll();
$salesMap = array_column($salesByDay, 'total', 'd');
$chartLabels = [];
$chartValues = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('M j', strtotime($date));
    $chartValues[] = round((float) ($salesMap[$date] ?? 0), 2);
}

$statusBreakdown = $pdo->query('SELECT order_status, COUNT(*) AS cnt FROM orders GROUP BY order_status')->fetchAll();
$statusLabels = array_map(fn ($r) => ucfirst($r['order_status']), $statusBreakdown);
$statusValues = array_map(fn ($r) => (int) $r['cnt'], $statusBreakdown);

$recentOrders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 6')->fetchAll();

$pageTitle = 'Dashboard';
$activeAdminPage = 'dashboard';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-stat-grid">
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-sack-dollar"></i></div><div class="num"><?= format_price($totalSales) ?></div><div class="label">Total Sales</div></div>
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-box"></i></div><div class="num"><?= $totalOrders ?></div><div class="label">Total Orders</div></div>
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-users"></i></div><div class="num"><?= $totalCustomers ?></div><div class="label">Total Customers</div></div>
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-flask"></i></div><div class="num"><?= $totalProducts ?></div><div class="label">Total Products</div></div>
</div>

<div class="admin-stat-grid" style="grid-template-columns:1fr 1fr;">
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-clock"></i></div><div class="num"><?= $pendingOrders ?></div><div class="label">Pending Orders</div></div>
  <div class="admin-stat-card"><div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="num"><?= count($lowStockProducts) ?></div><div class="label">Low Stock Products</div></div>
</div>

<div class="chart-grid">
  <div class="admin-card">
    <div class="admin-card-head"><h3 style="margin:0;">Sales — Last 14 Days</h3></div>
    <canvas id="salesChart" height="110"></canvas>
  </div>
  <div class="admin-card">
    <div class="admin-card-head"><h3 style="margin:0;">Orders by Status</h3></div>
    <canvas id="statusChart" height="110"></canvas>
  </div>
</div>

<div class="chart-grid">
  <div class="admin-card">
    <div class="admin-card-head">
      <h3 style="margin:0;">Recent Orders</h3>
      <a href="<?= e(base_url('admin/orders.php')) ?>" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td><a href="<?= e(base_url('admin/order-details.php?id=' . (int) $order['id'])) ?>">#<?= e($order['order_number']) ?></a></td>
            <td><?= e($order['customer_name']) ?></td>
            <td><?= format_price($order['total']) ?></td>
            <td><span class="status-pill status-<?= e($order['order_status']) ?>"><?= e(ucfirst($order['order_status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$recentOrders): ?><tr><td colspan="4" class="empty-row">No orders yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-head"><h3 style="margin:0;">Low Stock</h3></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Product</th><th>SKU</th><th>Stock</th></tr></thead>
        <tbody>
          <?php foreach ($lowStockProducts as $p): ?>
          <tr>
            <td><a href="<?= e(base_url('admin/edit-product.php?id=' . (int) $p['id'])) ?>"><?= e($p['name']) ?></a></td>
            <td><?= e($p['sku']) ?></td>
            <td style="color:<?= (int) $p['stock'] === 0 ? 'var(--error)' : 'var(--text-dark)' ?>;"><?= (int) $p['stock'] ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$lowStockProducts): ?><tr><td colspan="3" class="empty-row">All products are well stocked.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Sales ($)',
      data: <?= json_encode($chartValues) ?>,
      borderColor: '#B4837D',
      backgroundColor: 'rgba(180,131,125,0.12)',
      tension: 0.35,
      fill: true,
      pointRadius: 2,
    }],
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
});

new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      data: <?= json_encode($statusValues) ?>,
      backgroundColor: ['#D3BE93', '#8FAFC2', '#A79BD1', '#7FB0D8', '#8FC49A', '#D98F86'],
    }],
  },
  options: { plugins: { legend: { position: 'bottom' } } },
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
