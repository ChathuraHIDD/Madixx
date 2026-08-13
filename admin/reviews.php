<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = (string) ($_POST['action'] ?? '');
    $reviewId = (int) ($_POST['review_id'] ?? 0);

    $stmt = $pdo->prepare('SELECT product_id FROM reviews WHERE id = :id');
    $stmt->execute(['id' => $reviewId]);
    $review = $stmt->fetch();

    if ($review) {
        if ($action === 'approve') {
            $pdo->prepare('UPDATE reviews SET status = "approved" WHERE id = :id')->execute(['id' => $reviewId]);
            refresh_product_rating((int) $review['product_id']);
            flash('success', 'Review approved.');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM reviews WHERE id = :id')->execute(['id' => $reviewId]);
            refresh_product_rating((int) $review['product_id']);
            flash('success', 'Review deleted.');
        }
    }
    redirect('admin/reviews.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$status = trim((string) ($_GET['status'] ?? ''));
$where = $status !== '' ? 'WHERE r.status = :status' : '';

$sql = "SELECT r.*, u.name AS customer_name, p.name AS product_name, p.slug AS product_slug
        FROM reviews r JOIN users u ON u.id = r.user_id JOIN products p ON p.id = r.product_id
        $where ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
if ($status !== '') {
    $stmt->execute(['status' => $status]);
} else {
    $stmt->execute();
}
$reviews = $stmt->fetchAll();

$pageTitle = 'Reviews';
$activeAdminPage = 'reviews';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head">
    <h3 style="margin:0;"><?= count($reviews) ?> Reviews</h3>
    <div style="display:flex;gap:8px;">
      <a href="<?= e(base_url('admin/reviews.php')) ?>" class="btn btn-sm <?= $status === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
      <a href="<?= e(base_url('admin/reviews.php?status=pending')) ?>" class="btn btn-sm <?= $status === 'pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
      <a href="<?= e(base_url('admin/reviews.php?status=approved')) ?>" class="btn btn-sm <?= $status === 'approved' ? 'btn-primary' : 'btn-outline' ?>">Approved</a>
    </div>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($reviews as $r): ?>
        <tr>
          <td><a href="<?= e(base_url('product.php?slug=' . $r['product_slug'])) ?>" target="_blank"><?= e($r['product_name']) ?></a></td>
          <td><?= e($r['customer_name']) ?></td>
          <td><?= str_repeat('★', (int) $r['rating']) ?></td>
          <td style="max-width:320px;"><?= e(mb_strimwidth($r['review'], 0, 140, '…')) ?></td>
          <td><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
          <td><span class="status-pill status-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td>
            <div style="display:flex;gap:8px;">
              <?php if ($r['status'] === 'pending'): ?>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
              </form>
              <?php endif; ?>
              <form method="post" data-confirm="Delete this review?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$reviews): ?><tr><td colspan="7" class="empty-row">No reviews found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
