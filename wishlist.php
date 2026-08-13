<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_login();

$stmt = db()->prepare(
    'SELECT w.id AS wishlist_id, p.* FROM wishlist w
     JOIN products p ON p.id = w.product_id
     WHERE w.user_id = :uid ORDER BY w.created_at DESC'
);
$stmt->execute(['uid' => current_user_id()]);
$items = $stmt->fetchAll();

$pageTitle = 'My Wishlist — Glowelle';
$metaDescription = 'View and manage the products you have saved to your Glowelle wishlist.';
$canonicalPath = 'wishlist.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'Wishlist', 'url' => null]]) ?>
  <h1 style="font-size:2.2rem;margin-bottom:32px;">My Wishlist</h1>

  <?php if (!$items): ?>
  <div class="empty-state">
    <i class="fa-regular fa-heart"></i>
    <h2>Your wishlist is empty</h2>
    <p>Save the products you love and come back to them anytime.</p>
    <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Discover Products</a>
  </div>
  <?php else: ?>
  <div class="product-grid" id="wishlistGrid">
    <?php foreach ($items as $item): ?>
    <?php
      $hasSale = $item['sale_price'] !== null && (float) $item['sale_price'] < (float) $item['price'];
      $outOfStock = (int) $item['stock'] < 1;
    ?>
    <div class="product-card" data-wishlist-id="<?= (int) $item['wishlist_id'] ?>" data-product-id="<?= (int) $item['id'] ?>">
      <div class="product-card-media">
        <a href="<?= e(base_url('product.php?slug=' . $item['slug'])) ?>">
          <img src="<?= e(base_url($item['image'])) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
        </a>
        <button type="button" class="wishlist-toggle active" data-product-id="<?= (int) $item['id'] ?>" aria-label="Remove from wishlist" aria-pressed="true">
          <i class="fa-solid fa-heart"></i>
        </button>
      </div>
      <div class="product-card-body">
        <h3 class="product-name"><a href="<?= e(base_url('product.php?slug=' . $item['slug'])) ?>"><?= e($item['name']) ?></a></h3>
        <div class="product-price">
          <?php if ($hasSale): ?>
            <span class="price-sale"><?= format_price($item['sale_price']) ?></span>
            <span class="price-original"><?= format_price($item['price']) ?></span>
          <?php else: ?>
            <span class="price-regular"><?= format_price($item['price']) ?></span>
          <?php endif; ?>
        </div>
        <button type="button" class="btn btn-primary btn-block move-to-cart-btn" data-product-id="<?= (int) $item['id'] ?>"<?= $outOfStock ? ' disabled' : '' ?>>
          <?= $outOfStock ? 'Out of Stock' : 'Move to Cart' ?>
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
window.onWishlistPageUpdate = function (productId) {
  const card = document.querySelector('#wishlistGrid .product-card[data-product-id="' + productId + '"]');
  card?.remove();
  if (!document.querySelectorAll('#wishlistGrid .product-card').length) {
    window.location.reload();
  }
};

document.querySelectorAll('.move-to-cart-btn').forEach((btn) => {
  btn.addEventListener('click', async () => {
    if (btn.disabled) return;
    const CFG = window.GLOWELLE;
    btn.disabled = true;
    const productId = btn.dataset.productId;
    try {
      const addRes = await fetch(CFG.baseUrl + 'ajax/cart_add.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ csrf_token: CFG.csrfToken, product_id: productId, quantity: 1 }),
      });
      const addData = await addRes.json();
      if (!addData.success) {
        window.showToast(addData.message || 'Could not add item.', 'error');
        btn.disabled = false;
        return;
      }
      document.querySelectorAll('#cartCount, .cart-count').forEach((el) => (el.textContent = addData.cart_count));

      const wlRes = await fetch(CFG.baseUrl + 'ajax/wishlist_toggle.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ csrf_token: CFG.csrfToken, product_id: productId }),
      });
      await wlRes.json();

      window.showToast('Moved to your bag.');
      window.onWishlistPageUpdate(productId);
    } catch (err) {
      window.showToast('Something went wrong. Please try again.', 'error');
      btn.disabled = false;
    }
  });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
