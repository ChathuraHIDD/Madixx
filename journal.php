<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;

$categories = ['Style Guides', 'Lens Guides', 'Care Guides', 'Frame Guides', 'MADIXX News'];

$featured = null;
if ($categoryFilter === '' && $searchQuery === '' && $page === 1) {
    $featStmt = db()->query('SELECT * FROM blog_posts WHERE status = "published" ORDER BY created_at DESC LIMIT 1');
    $featured = $featStmt->fetch();
}

$where = ['status = "published"'];
$params = [];
if ($featured) {
    $where[] = 'id != :featured_id';
    $params['featured_id'] = $featured['id'];
}
if ($categoryFilter !== '') {
    $where[] = 'category = :category';
    $params['category'] = $categoryFilter;
}
if ($searchQuery !== '') {
    $where[] = '(title LIKE :q1 OR excerpt LIKE :q2)';
    $needle = '%' . $searchQuery . '%';
    $params['q1'] = $needle;
    $params['q2'] = $needle;
}
$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $page] = paginate($total, $perPage, $page);

$stmt = db()->prepare("SELECT * FROM blog_posts WHERE $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$pageTitle = 'The MADIXX Journal — Style & Care Guides';
$metaDescription = 'Style guides, lens explainers, frame care tips and eyewear stories from the MADIXX team.';
$canonicalPath = 'journal.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'Journal', 'url' => null]]) ?>
  <div class="section-heading" style="margin-bottom:32px;">
    <p class="eyebrow">The MADIXX Journal</p>
    <h1 style="font-size:2.2rem;">Style Journal</h1>
    <p>Frame guides, lens explainers and care tips from our team.</p>
  </div>

  <?php if ($featured): ?>
  <a href="<?= e(base_url('journal-post.php?slug=' . $featured['slug'])) ?>" class="featured-post">
    <img src="<?= e(base_url($featured['featured_image'])) ?>" alt="<?= e($featured['title']) ?>" loading="lazy">
    <div>
      <p class="post-category"><?= e($featured['category']) ?></p>
      <h2 style="font-size:1.8rem;margin-bottom:12px;"><?= e($featured['title']) ?></h2>
      <p style="color:var(--text-muted);"><?= e($featured['excerpt']) ?></p>
      <p class="post-meta">By <?= e($featured['author']) ?> · <?= e(date('M j, Y', strtotime($featured['created_at']))) ?></p>
    </div>
  </a>
  <?php endif; ?>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:32px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?= e(base_url('journal.php')) ?>" class="btn btn-sm <?= $categoryFilter === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= e(base_url('journal.php?category=' . urlencode($cat))) ?>" class="btn btn-sm <?= $categoryFilter === $cat ? 'btn-primary' : 'btn-outline' ?>"><?= e($cat) ?></a>
      <?php endforeach; ?>
    </div>
    <form method="get" style="display:flex;gap:0;">
      <input type="search" name="q" placeholder="Search journal…" value="<?= e($searchQuery) ?>" style="border-radius:var(--radius-sm) 0 0 var(--radius-sm);">
      <button type="submit" class="btn btn-primary" style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;">Search</button>
    </form>
  </div>

  <?php if ($posts): ?>
  <div class="blog-grid">
    <?php foreach ($posts as $post): ?>
    <a href="<?= e(base_url('journal-post.php?slug=' . $post['slug'])) ?>" class="blog-card">
      <img src="<?= e(base_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
      <p class="post-category"><?= e($post['category']) ?></p>
      <h3><?= e($post['title']) ?></h3>
      <p><?= e($post['excerpt']) ?></p>
      <p class="post-meta"><?= e(date('M j, Y', strtotime($post['created_at']))) ?></p>
    </a>
    <?php endforeach; ?>
  </div>
  <?= render_pagination($page, $totalPages) ?>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa-regular fa-newspaper"></i>
    <h2>No articles found</h2>
    <p>Try a different category or search term.</p>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
