<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$product = $slug !== '' ? get_product_by_slug($slug) : null;

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found — MADIXX';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><i class="fa-regular fa-face-frown"></i><h2>Product not found</h2><p>This product may have been removed or is no longer available.</p><a href="' . e(base_url('shop.php')) . '" class="btn btn-primary">Continue Shopping</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$gallery = get_product_gallery((int) $product['id'], $product['image']);
$reviews = get_product_reviews((int) $product['id']);
$wishlistIds = get_wishlist_product_ids();
$inWishlist = in_array((int) $product['id'], $wishlistIds, true);
$hasSale = $product['sale_price'] !== null && (float) $product['sale_price'] < (float) $product['price'];
$discount = $hasSale ? (int) round((1 - ((float) $product['sale_price'] / (float) $product['price'])) * 100) : 0;
$outOfStock = (int) $product['stock'] < 1;

$benefits = array_values(array_filter(array_map('trim', explode("\n", (string) $product['benefits']))));
$ingredientsList = array_values(array_filter(array_map('trim', explode(',', (string) $product['ingredients']))));

$user = current_user();
$userHasReviewed = false;
if ($user) {
    $checkStmt = db()->prepare('SELECT id FROM reviews WHERE user_id = :uid AND product_id = :pid');
    $checkStmt->execute(['uid' => $user['id'], 'pid' => $product['id']]);
    $userHasReviewed = (bool) $checkStmt->fetch();
}

// Related products from the same category.
$relatedStmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.category_id = :cid AND p.id != :pid AND p.status = "active"
     ORDER BY p.is_bestseller DESC, p.rating DESC LIMIT 4'
);
$relatedStmt->execute(['cid' => $product['category_id'], 'pid' => $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();

$pageTitle = $product['name'] . ' — MADIXX';
$metaDescription = $product['short_description'] ?: $product['name'];
$canonicalPath = 'product.php?slug=' . $product['slug'];
$ogImage = base_url($product['image']);

require __DIR__ . '/includes/header.php';
?>

<div class="container">
  <?= render_breadcrumbs([
      ['label' => 'Shop', 'url' => base_url('shop.php')],
      ['label' => $product['category_name'], 'url' => base_url('shop.php?category=' . $product['category_slug'])],
      ['label' => $product['name'], 'url' => null],
  ]) ?>

  <div class="pdp-layout">
    <div class="pdp-gallery">
      <div class="pdp-gallery-main">
        <img src="<?= e(base_url($gallery[0])) ?>" alt="<?= e($product['name']) ?>" id="pdpMainImage">
      </div>
      <?php if (count($gallery) > 1): ?>
      <div class="pdp-thumbs">
        <?php foreach ($gallery as $i => $img): ?>
        <div class="pdp-thumb<?= $i === 0 ? ' active' : '' ?>">
          <img src="<?= e(base_url($img)) ?>" alt="<?= e($product['name']) ?> view <?= $i + 1 ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="pdp-info">
      <p class="pdp-category"><?= e($product['category_name']) ?><?= $product['product_type'] ? ' · ' . e($product['product_type']) : '' ?></p>
      <h1><?= e($product['name']) ?></h1>

      <div class="pdp-rating">
        <?= star_rating_html((float) $product['rating']) ?>
        <a href="#reviews"><?= (int) $product['review_count'] ?> review<?= (int) $product['review_count'] === 1 ? '' : 's' ?></a>
      </div>

      <div class="pdp-price">
        <?php if ($hasSale): ?>
          <span class="price-sale"><?= format_price($product['sale_price']) ?></span>
          <span class="price-original"><?= format_price($product['price']) ?></span>
          <span class="badge badge-sale" style="position:static;display:inline-block;">-<?= $discount ?>%</span>
        <?php else: ?>
          <span class="price-regular"><?= format_price($product['price']) ?></span>
        <?php endif; ?>
      </div>

      <p class="pdp-short-desc"><?= e($product['short_description']) ?></p>

      <p class="stock-status <?= $outOfStock ? 'out-stock' : 'in-stock' ?>">
        <i class="fa-solid <?= $outOfStock ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
        <?= $outOfStock ? 'Out of Stock' : 'In Stock — ready to ship' ?>
      </p>

      <div class="qty-selector">
        <button type="button" class="qty-minus" aria-label="Decrease quantity">−</button>
        <input type="number" class="js-qty-input" value="1" min="1" data-max="<?= (int) $product['stock'] ?>" aria-label="Quantity">
        <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
      </div>

      <div class="pdp-actions">
        <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="<?= (int) $product['id'] ?>"<?= $outOfStock ? ' disabled' : '' ?>>
          <?= $outOfStock ? 'Out of Stock' : 'Add to Cart' ?>
        </button>
        <a href="<?= e(base_url('checkout.php?buy_now=' . (int) $product['id'])) ?>" class="btn btn-outline"<?= $outOfStock ? ' aria-disabled="true" onclick="return false;"' : '' ?>>Buy Now</a>
        <button type="button" class="pdp-wishlist-btn wishlist-toggle<?= $inWishlist ? ' active' : '' ?>" data-product-id="<?= (int) $product['id'] ?>" aria-label="Add to wishlist" aria-pressed="<?= $inWishlist ? 'true' : 'false' ?>">
          <i class="fa-<?= $inWishlist ? 'solid' : 'regular' ?> fa-heart"></i>
        </button>
      </div>

      <div class="pdp-meta">
        <div><strong>SKU:</strong> <?= e($product['sku']) ?></div>
        <div><strong>Brand:</strong> <?= e($product['brand']) ?></div>
        <?php if ($product['skin_type']): ?><div><strong>Frame Material:</strong> <?= e($product['skin_type']) ?></div><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="pdp-tabs">
    <div class="tab-nav">
      <button type="button" class="tab-btn active" data-tab="tab-benefits">Why You'll Love It</button>
      <button type="button" class="tab-btn" data-tab="tab-ingredients">Frame &amp; Lens Details</button>
      <button type="button" class="tab-btn" data-tab="tab-howto">Care &amp; Fit</button>
      <button type="button" class="tab-btn" data-tab="tab-reviews">Reviews (<?= (int) $product['review_count'] ?>)</button>
    </div>

    <div class="tab-panel active" id="tab-benefits">
      <?php if ($benefits): ?>
      <ul class="benefit-list">
        <?php foreach ($benefits as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
      </ul>
      <?php else: ?><p><?= nl2br(e($product['description'])) ?></p><?php endif; ?>
    </div>

    <div class="tab-panel" id="tab-ingredients">
      <?php if ($ingredientsList): ?>
      <ul class="ingredient-list">
        <?php foreach ($ingredientsList as $ing): ?><li><?= e($ing) ?></li><?php endforeach; ?>
      </ul>
      <?php else: ?><p>Full specifications available on request.</p><?php endif; ?>
    </div>

    <div class="tab-panel" id="tab-howto">
      <p><?= nl2br(e($product['how_to_use'])) ?></p>
      <?php if ($product['skin_type']): ?>
      <p style="margin-top:20px;"><strong>Frame material:</strong> <?= e($product['skin_type']) ?></p>
      <?php endif; ?>
    </div>

    <div class="tab-panel" id="tab-reviews">
      <div id="reviews">
        <div class="review-summary">
          <span class="avg-rating"><?= number_format((float) $product['rating'], 1) ?></span>
          <div>
            <?= star_rating_html((float) $product['rating']) ?>
            <p style="color:var(--text-muted);margin:4px 0 0;">Based on <?= (int) $product['review_count'] ?> review<?= (int) $product['review_count'] === 1 ? '' : 's' ?></p>
          </div>
        </div>

        <?php if ($user && !$userHasReviewed): ?>
        <form id="reviewForm" class="form-group" data-product-id="<?= (int) $product['id'] ?>" style="border:1px solid var(--border-soft);border-radius:var(--radius-md);padding:24px;margin-bottom:32px;">
          <?= csrf_field() ?>
          <h4 style="margin-bottom:16px;">Write a Review</h4>
          <div class="form-group">
            <label>Your Rating</label>
            <select name="rating" required>
              <option value="">Select a rating</option>
              <option value="5">★★★★★ Excellent</option>
              <option value="4">★★★★ Very Good</option>
              <option value="3">★★★ Average</option>
              <option value="2">★★ Poor</option>
              <option value="1">★ Very Poor</option>
            </select>
          </div>
          <div class="form-group">
            <label>Your Review</label>
            <textarea name="review" required minlength="10" placeholder="Share your experience with this product…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit Review</button>
          <p id="reviewFormMessage" style="margin-top:12px;font-size:0.85rem;"></p>
        </form>
        <?php elseif (!$user): ?>
        <p style="margin-bottom:32px;"><a href="<?= e(base_url('login.php')) ?>" style="text-decoration:underline;">Log in</a> to write a review.</p>
        <?php else: ?>
        <p style="margin-bottom:32px;color:var(--text-muted);">You've already reviewed this product. Thank you!</p>
        <?php endif; ?>

        <?php if ($reviews): ?>
          <?php foreach ($reviews as $r): ?>
          <div class="review-item">
            <div class="review-item-head">
              <div>
                <strong><?= e($r['customer_name']) ?></strong>
                <div><?= star_rating_html((float) $r['rating']) ?></div>
              </div>
              <span class="review-date"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></span>
            </div>
            <p><?= nl2br(e($r['review'])) ?></p>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:var(--text-muted);">No reviews yet — be the first to share your thoughts.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script type="application/ld+json">
  <?= json_encode([
      '@context'    => 'https://schema.org/',
      '@type'       => 'Product',
      'name'        => $product['name'],
      'image'       => [$ogImage],
      'description' => $product['short_description'],
      'sku'         => $product['sku'],
      'brand'       => ['@type' => 'Brand', 'name' => $product['brand']],
      'offers'      => [
          '@type'         => 'Offer',
          'url'           => base_url($canonicalPath),
          'priceCurrency' => 'USD',
          'price'         => $hasSale ? $product['sale_price'] : $product['price'],
          'availability'  => $outOfStock ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
      ],
      'aggregateRating' => (int) $product['review_count'] > 0 ? [
          '@type'       => 'AggregateRating',
          'ratingValue' => (float) $product['rating'],
          'reviewCount' => (int) $product['review_count'],
      ] : null,
  ], JSON_UNESCAPED_SLASHES) ?>
  </script>

  <?php if ($relatedProducts): ?>
  <section class="section fade-in">
    <div class="section-heading text-center">
      <p class="eyebrow">You May Also Like</p>
      <h2>Complete Your Ritual</h2>
    </div>
    <div class="product-grid">
      <?php foreach ($relatedProducts as $relatedProduct): $product = $relatedProduct; ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<script>
document.getElementById('reviewForm')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.target;
  const msg = document.getElementById('reviewFormMessage');
  const data = new URLSearchParams(new FormData(form));
  data.set('product_id', form.dataset.productId);
  const res = await fetch(window.MADIXX.baseUrl + 'ajax/submit_review.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: data,
  });
  const json = await res.json();
  msg.textContent = json.message;
  msg.style.color = json.success ? 'var(--success)' : 'var(--error)';
  if (json.success) {
    form.reset();
    setTimeout(() => window.location.reload(), 1200);
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
