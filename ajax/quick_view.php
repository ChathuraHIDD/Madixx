<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

$slug = $_GET['slug'] ?? '';
$product = get_product_by_slug($slug);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$hasSale = $product['sale_price'] !== null && (float) $product['sale_price'] < (float) $product['price'];
$outOfStock = (int) $product['stock'] < 1;

ob_start();
?>
<button type="button" class="modal-close" id="quickViewClose" aria-label="Close">&times;</button>
<div class="quick-view-grid">
  <img src="<?= e(base_url($product['image'])) ?>" alt="<?= e($product['name']) ?>">
  <div>
    <p class="pdp-category"><?= e($product['category_name']) ?></p>
    <h2><?= e($product['name']) ?></h2>
    <div class="pdp-rating">
      <?= star_rating_html((float) $product['rating']) ?>
      <span class="review-count">(<?= (int) $product['review_count'] ?> reviews)</span>
    </div>
    <div class="pdp-price">
      <?php if ($hasSale): ?>
        <span class="price-sale"><?= format_price($product['sale_price']) ?></span>
        <span class="price-original"><?= format_price($product['price']) ?></span>
      <?php else: ?>
        <span class="price-regular"><?= format_price($product['price']) ?></span>
      <?php endif; ?>
    </div>
    <p class="pdp-short-desc"><?= e($product['short_description']) ?></p>
    <div class="pdp-actions">
      <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="<?= (int) $product['id'] ?>"<?= $outOfStock ? ' disabled' : '' ?>>
        <?= $outOfStock ? 'Out of Stock' : 'Add to Cart' ?>
      </button>
      <a href="<?= e(base_url('product.php?slug=' . $product['slug'])) ?>" class="btn btn-outline">View Full Details</a>
    </div>
  </div>
</div>
<?php
$html = ob_get_clean();

echo json_encode(['success' => true, 'html' => $html]);
