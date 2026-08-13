<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Collections — Glowelle';
$metaDescription = 'Explore curated Glowelle collections: bestsellers, new arrivals, featured formulas and the Glow Essentials edit.';
$canonicalPath = 'collections.php';

$wishlistIds = get_wishlist_product_ids();
$pdo = db();

function fetch_shelf(PDO $pdo, string $flagColumn, int $limit = 4): array
{
    $stmt = $pdo->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p JOIN categories c ON c.id = p.category_id
         WHERE p.status = 'active' AND p.$flagColumn = 1
         ORDER BY p.created_at DESC LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

$shelves = [
    ['title' => 'Bestsellers', 'desc' => 'The formulas our customers can\'t live without.', 'items' => fetch_shelf($pdo, 'is_bestseller')],
    ['title' => 'New Arrivals', 'desc' => 'Just landed — fresh additions to the Glowelle edit.', 'items' => fetch_shelf($pdo, 'is_new')],
    ['title' => 'Featured', 'desc' => 'This season\'s must-haves, hand-picked by our team.', 'items' => fetch_shelf($pdo, 'is_featured')],
];

$glowStmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'active' AND c.slug = 'glow-essentials'
     ORDER BY p.created_at DESC"
);
$glowStmt->execute();
$glowEssentials = $glowStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?= render_breadcrumbs([['label' => 'Collections', 'url' => null]]) ?>
  <div class="section-heading" style="margin-bottom:8px;">
    <p class="eyebrow">Curated For You</p>
    <h1 style="font-size:2.2rem;">Collections</h1>
    <p>Shelves curated around what you'll love most.</p>
  </div>
</div>

<?php foreach ($shelves as $shelf): if (!$shelf['items']) continue; ?>
<section class="section-tight fade-in">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:28px;">
      <div>
        <h2 style="margin-bottom:4px;"><?= e($shelf['title']) ?></h2>
        <p style="color:var(--text-muted);"><?= e($shelf['desc']) ?></p>
      </div>
    </div>
    <div class="product-grid">
      <?php foreach ($shelf['items'] as $product): ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<?php if ($glowEssentials): ?>
<section class="section-tight fade-in" style="background:var(--color-blush);">
  <div class="container">
    <div style="margin-bottom:28px;">
      <h2 style="margin-bottom:4px;">Glow Essentials</h2>
      <p style="color:var(--text-muted);">Our most-loved beauty products, curated into one edit.</p>
    </div>
    <div class="product-grid">
      <?php foreach ($glowEssentials as $product): ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
