<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$query = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$wishlistIds = get_wishlist_product_ids();
$products = [];
$total = 0;
$totalPages = 1;

if ($query !== '') {
    $filters = [
        'search' => $query,
        'sort'   => $_GET['sort'] ?? 'featured',
    ];
    [$products, $total, $totalPages, $page] = query_products($filters, $page, 12);
}

$pageTitle = 'Search results for "' . $query . '" — Glowelle';
$metaDescription = 'Search results for "' . $query . '" on Glowelle.';
$canonicalPath = 'search.php?q=' . urlencode($query);

require __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?= render_breadcrumbs([['label' => 'Search', 'url' => null]]) ?>

  <div class="section-heading" style="margin-bottom:32px;">
    <p class="eyebrow">Search</p>
    <h1 style="font-size:2.2rem;">Search results for &ldquo;<?= e($query) ?>&rdquo;</h1>
    <p><?= (int) $total ?> product<?= $total === 1 ? '' : 's' ?> found</p>
  </div>

  <?php if ($query === ''): ?>
  <div class="empty-state">
    <i class="fa-solid fa-magnifying-glass"></i>
    <h2>What are you looking for?</h2>
    <p>Try searching for a product name, category or brand.</p>
  </div>
  <?php elseif ($products): ?>
  <div class="product-grid">
    <?php foreach ($products as $product): ?>
      <?php include __DIR__ . '/includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?= render_pagination($page, $totalPages) ?>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa-regular fa-face-frown"></i>
    <h2>No results found</h2>
    <p>We couldn't find anything matching &ldquo;<?= e($query) ?>&rdquo;. Try a different search term.</p>
    <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Browse All Products</a>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
