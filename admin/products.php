<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_require();
    $pdo->prepare('DELETE FROM products WHERE id = :id')->execute(['id' => (int) $_POST['product_id']]);
    flash('success', 'Product deleted.');
    redirect('admin/products.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(p.name LIKE :s1 OR p.sku LIKE :s2)';
    $needle = '%' . $search . '%';
    $params['s1'] = $needle;
    $params['s2'] = $needle;
}
if ($categoryId > 0) {
    $where[] = 'p.category_id = :cat';
    $params['cat'] = $categoryId;
}
$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $page] = paginate($total, $perPage, $page);

$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id
     WHERE $whereSql ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$pageTitle = 'Products';
$activeAdminPage = 'products';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3 style="margin:0;"><?= (int) $total ?> Products</h3>
    <a href="<?= e(base_url('admin/add-product.php')) ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Product</a>
  </div>

  <form method="get" class="filter-bar">
    <input type="search" name="q" placeholder="Search by name or SKU…" value="<?= e($search) ?>">
    <select name="category_id" onchange="this.form.submit()">
      <option value="0">All Categories</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?= (int) $cat['id'] ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Flags</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><img src="<?= e(base_url($p['image'])) ?>" class="table-thumb" alt=""></td>
          <td><a href="<?= e(base_url('admin/edit-product.php?id=' . (int) $p['id'])) ?>"><?= e($p['name']) ?></a><br><small style="color:var(--text-light);"><?= e($p['sku']) ?></small></td>
          <td><?= e($p['category_name']) ?></td>
          <td>
            <?php if ($p['sale_price'] !== null): ?>
            <strong><?= format_price($p['sale_price']) ?></strong> <span style="text-decoration:line-through;color:var(--text-light);"><?= format_price($p['price']) ?></span>
            <?php else: ?><?= format_price($p['price']) ?><?php endif; ?>
          </td>
          <td style="color:<?= (int) $p['stock'] <= 10 ? 'var(--error)' : 'var(--text-dark)' ?>;"><?= (int) $p['stock'] ?></td>
          <td style="font-size:0.75rem;">
            <?= $p['is_featured'] ? '<span class="tag-yes">Featured</span> ' : '' ?>
            <?= $p['is_bestseller'] ? '<span class="tag-yes">Bestseller</span> ' : '' ?>
            <?= $p['is_new'] ? '<span class="tag-yes">New</span>' : '' ?>
          </td>
          <td><span class="status-pill status-<?= $p['status'] === 'active' ? 'active' : 'disabled' ?>"><?= e(ucfirst($p['status'])) ?></span></td>
          <td>
            <div style="display:flex;gap:8px;">
              <a href="<?= e(base_url('admin/edit-product.php?id=' . (int) $p['id'])) ?>" class="btn btn-outline btn-sm">Edit</a>
              <form method="post" data-confirm="Delete this product? This cannot be undone.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="8" class="empty-row">No products found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?= render_pagination($page, $totalPages) ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
