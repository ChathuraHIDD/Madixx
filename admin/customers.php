<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    csrf_require();
    $custId = (int) $_POST['customer_id'];
    $newStatus = (string) $_POST['new_status'] === 'active' ? 'active' : 'disabled';
    $pdo->prepare('UPDATE users SET status = :status WHERE id = :id AND role = "customer"')
        ->execute(['status' => $newStatus, 'id' => $custId]);
    flash('success', 'Customer status updated.');
    redirect('admin/customers.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = ['role = "customer"'];
$params = [];
if ($search !== '') {
    $where[] = '(name LIKE :s1 OR email LIKE :s2)';
    $needle = '%' . $search . '%';
    $params['s1'] = $needle;
    $params['s2'] = $needle;
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $page] = paginate($total, $perPage, $page);

$stmt = $pdo->prepare(
    "SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
     FROM users u WHERE $whereSql ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll();

$pageTitle = 'Customers';
$activeAdminPage = 'customers';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head"><h3 style="margin:0;"><?= (int) $total ?> Customers</h3></div>

  <form method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Search by name or email…" value="<?= e($search) ?>">
    <button type="submit" class="btn btn-outline btn-sm">Search</button>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td><a href="<?= e(base_url('admin/customer-details.php?id=' . (int) $c['id'])) ?>"><?= e($c['name']) ?></a></td>
          <td><?= e($c['email']) ?></td>
          <td><?= e((string) $c['phone']) ?></td>
          <td><?= (int) $c['order_count'] ?></td>
          <td><?= e(date('M j, Y', strtotime($c['created_at']))) ?></td>
          <td><span class="status-pill status-<?= e($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></td>
          <td>
            <form method="post" data-confirm="<?= $c['status'] === 'active' ? 'Disable' : 'Enable' ?> this account?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_status">
              <input type="hidden" name="customer_id" value="<?= (int) $c['id'] ?>">
              <input type="hidden" name="new_status" value="<?= $c['status'] === 'active' ? 'disabled' : 'active' ?>">
              <button type="submit" class="btn btn-outline btn-sm"><?= $c['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$customers): ?><tr><td colspan="7" class="empty-row">No customers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?= render_pagination($page, $totalPages) ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
