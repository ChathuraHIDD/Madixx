<?php

declare(strict_types=1);

/**
 * Reusable product card partial.
 * Expects $product (array, joined with categories as category_name) in scope.
 * Optionally $wishlistIds (array of product ids currently on the wishlist).
 */
$inWishlist = in_array((int) $product['id'], $wishlistIds ?? [], true);
$hasSale = $product['sale_price'] !== null && (float) $product['sale_price'] < (float) $product['price'];
$discount = $hasSale ? (int) round((1 - ((float) $product['sale_price'] / (float) $product['price'])) * 100) : 0;
$outOfStock = (int) $product['stock'] <= 0;
$productUrl = base_url('product.php?slug=' . rawurlencode($product['slug']));
?>
<div class="product-card" data-product-id="<?= (int) $product['id'] ?>">
  <div class="product-card-media">
    <a href="<?= e($productUrl) ?>" class="product-card-image-link">
      <img src="<?= e(base_url($product['image'])) ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="400" height="400">
    </a>
    <?php if ($hasSale): ?><span class="badge badge-sale">-<?= $discount ?>%</span>
    <?php elseif (!empty($product['is_new'])): ?><span class="badge badge-new">New</span>
    <?php elseif (!empty($product['is_bestseller'])): ?><span class="badge badge-bestseller">Bestseller</span>
    <?php endif; ?>
    <button type="button" class="wishlist-toggle<?= $inWishlist ? ' active' : '' ?>" data-product-id="<?= (int) $product['id'] ?>" aria-label="Add to wishlist" aria-pressed="<?= $inWishlist ? 'true' : 'false' ?>">
      <i class="fa-<?= $inWishlist ? 'solid' : 'regular' ?> fa-heart"></i>
    </button>
    <button type="button" class="quick-view-btn" data-product-slug="<?= e($product['slug']) ?>">Quick View</button>
  </div>
  <div class="product-card-body">
    <p class="product-category"><?= e($product['category_name'] ?? '') ?></p>
    <h3 class="product-name"><a href="<?= e($productUrl) ?>"><?= e($product['name']) ?></a></h3>
    <p class="product-desc"><?= e($product['short_description'] ?? '') ?></p>
    <div class="product-rating">
      <?= star_rating_html((float) $product['rating']) ?>
      <span class="review-count">(<?= (int) $product['review_count'] ?>)</span>
    </div>
    <div class="product-price">
      <?php if ($hasSale): ?>
        <span class="price-sale"><?= format_price($product['sale_price']) ?></span>
        <span class="price-original"><?= format_price($product['price']) ?></span>
      <?php else: ?>
        <span class="price-regular"><?= format_price($product['price']) ?></span>
      <?php endif; ?>
    </div>
    <button type="button" class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?= (int) $product['id'] ?>"<?= $outOfStock ? ' disabled' : '' ?>>
      <?= $outOfStock ? 'Out of Stock' : 'Add to Cart' ?>
    </button>
  </div>
</div>
