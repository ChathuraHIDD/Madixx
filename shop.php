<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$categorySlug = trim((string) ($_GET['category'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

$categoryStmt = db()->query('SELECT id, name, slug, description FROM categories ORDER BY name ASC');
$allCategories = $categoryStmt->fetchAll();

$activeCategory = null;
if ($categorySlug !== '') {
    foreach ($allCategories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $activeCategory = $cat;
            break;
        }
    }
}

$filters = [
    'category'     => $categorySlug ?: null,
    'product_type' => $_GET['type'] ?? null,
    'skin_type'    => $_GET['skin_type'] ?? null,
    'brand'        => $_GET['brand'] ?? null,
    'min_price'    => $_GET['min_price'] ?? null,
    'max_price'    => $_GET['max_price'] ?? null,
    'min_rating'   => $_GET['min_rating'] ?? null,
    'availability' => $_GET['availability'] ?? null,
    'sort'         => $_GET['sort'] ?? 'featured',
];

[$products, $total, $totalPages, $page] = query_products($filters, $page, 12);

// Distinct filter option values (scoped to the active category when set).
$typeSql = 'SELECT DISTINCT p.product_type FROM products p JOIN categories c ON c.id = p.category_id WHERE p.status = "active" AND p.product_type IS NOT NULL';
$brandSql = 'SELECT DISTINCT p.brand FROM products p JOIN categories c ON c.id = p.category_id WHERE p.status = "active"';
$params = [];
if ($activeCategory) {
    $typeSql .= ' AND c.slug = :slug';
    $brandSql .= ' AND c.slug = :slug';
    $params['slug'] = $activeCategory['slug'];
}
$typeStmt = db()->prepare($typeSql . ' ORDER BY p.product_type ASC');
$typeStmt->execute($params);
$productTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

$brandStmt = db()->prepare($brandSql . ' ORDER BY p.brand ASC');
$brandStmt->execute($params);
$brands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);

$skinTypes = ['Acetate', 'Metal', 'Titanium', 'Acetate & Metal', 'Vegan Leather', 'Molded Shell', 'Microfiber'];

$wishlistIds = get_wishlist_product_ids();

$pageTitle = ($activeCategory ? $activeCategory['name'] : 'Shop All') . ' — MADIXX';
$metaDescription = $activeCategory ? $activeCategory['description'] : 'Shop the full MADIXX collection of sunglasses, spectacles and eyewear accessories.';
$canonicalPath = 'shop.php' . ($categorySlug ? '?category=' . $categorySlug : '');

require __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?= render_breadcrumbs([
      ['label' => 'Shop', 'url' => $activeCategory ? base_url('shop.php') : null],
      ...($activeCategory ? [['label' => $activeCategory['name'], 'url' => null]] : []),
  ]) ?>

  <div class="section-heading" style="margin-bottom:32px;">
    <p class="eyebrow">Shop</p>
    <h1 style="font-size:2.2rem;"><?= e($activeCategory ? $activeCategory['name'] : 'All Products') ?></h1>
    <?php if ($activeCategory): ?><p><?= e($activeCategory['description']) ?></p><?php endif; ?>
  </div>

  <div class="shop-layout">
    <aside class="filters-panel" id="filtersPanel">
      <form id="filtersForm" method="get">
        <?php if ($categorySlug !== ''): ?><input type="hidden" name="category" value="<?= e($categorySlug) ?>"><?php endif; ?>
        <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">

        <?php if (!$activeCategory): ?>
        <div class="filter-group">
          <h4>Category</h4>
          <?php foreach ($allCategories as $cat): ?>
          <label class="filter-option">
            <input type="radio" name="category" value="<?= e($cat['slug']) ?>" onchange="this.form.submit()">
            <?= e($cat['name']) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($productTypes): ?>
        <div class="filter-group">
          <h4>Product Type</h4>
          <?php foreach ($productTypes as $type): ?>
          <label class="filter-option">
            <input type="radio" name="type" value="<?= e($type) ?>" <?= ($filters['product_type'] ?? '') === $type ? 'checked' : '' ?>>
            <?= e($type) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="filter-group">
          <h4>Skin Type</h4>
          <?php foreach ($skinTypes as $st): ?>
          <label class="filter-option">
            <input type="radio" name="skin_type" value="<?= e($st) ?>" <?= ($filters['skin_type'] ?? '') === $st ? 'checked' : '' ?>>
            <?= e($st) ?>
          </label>
          <?php endforeach; ?>
        </div>

        <?php if ($brands): ?>
        <div class="filter-group">
          <h4>Brand</h4>
          <?php foreach ($brands as $brand): ?>
          <label class="filter-option">
            <input type="radio" name="brand" value="<?= e($brand) ?>" <?= ($filters['brand'] ?? '') === $brand ? 'checked' : '' ?>>
            <?= e($brand) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="filter-group">
          <h4>Price Range</h4>
          <div class="form-row" style="gap:10px;">
            <input type="number" name="min_price" placeholder="Min" min="0" value="<?= e((string) ($_GET['min_price'] ?? '')) ?>">
            <input type="number" name="max_price" placeholder="Max" min="0" value="<?= e((string) ($_GET['max_price'] ?? '')) ?>">
          </div>
          <button type="submit" class="btn btn-outline btn-sm btn-block" style="margin-top:10px;">Apply</button>
        </div>

        <div class="filter-group">
          <h4>Rating</h4>
          <?php foreach ([4, 3] as $r): ?>
          <label class="filter-option">
            <input type="radio" name="min_rating" value="<?= $r ?>" <?= (string) ($filters['min_rating'] ?? '') === (string) $r ? 'checked' : '' ?> onchange="this.form.submit()">
            <?= str_repeat('★', $r) ?>&nbsp;&amp; up
          </label>
          <?php endforeach; ?>
        </div>

        <div class="filter-group">
          <h4>Availability</h4>
          <label class="filter-option">
            <input type="checkbox" name="availability" value="in_stock" <?= ($filters['availability'] ?? '') === 'in_stock' ? 'checked' : '' ?> onchange="this.form.submit()">
            In Stock Only
          </label>
        </div>

        <a href="<?= e(base_url('shop.php' . ($categorySlug ? '?category=' . $categorySlug : ''))) ?>" class="btn-text">Clear Filters</a>
      </form>
    </aside>

    <div class="shop-results">
      <div class="shop-toolbar">
        <button type="button" class="btn btn-outline btn-sm mobile-filter-toggle" id="mobileFilterToggle"><i class="fa-solid fa-sliders"></i> Filters</button>
        <p class="result-count"><?= (int) $total ?> product<?= $total === 1 ? '' : 's' ?></p>
        <form method="get" class="sort-select">
          <?php foreach (['category', 'type', 'skin_type', 'brand', 'min_price', 'max_price', 'min_rating', 'availability'] as $k): if (isset($_GET[$k])): ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e((string) $_GET[$k]) ?>">
          <?php endif; endforeach; ?>
          <select name="sort" onchange="this.form.submit()">
            <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Featured</option>
            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="bestselling" <?= $filters['sort'] === 'bestselling' ? 'selected' : '' ?>>Best Selling</option>
            <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
          </select>
        </form>
      </div>

      <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?= render_pagination($page, $totalPages) ?>
      <?php else: ?>
      <div class="empty-state">
        <i class="fa-regular fa-face-frown"></i>
        <h2>No products found</h2>
        <p>Try adjusting your filters or browse our full collection.</p>
        <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">View All Products</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
