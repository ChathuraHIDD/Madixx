<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'MADIXX — Beauty, Refined. Premium Skincare & Cosmetics';
$metaDescription = 'Discover MADIXX: premium skincare and cosmetics designed to enhance your natural glow. Shop bestselling serums, foundation, body care and more.';
$canonicalPath = '';

$wishlistIds = get_wishlist_product_ids();

$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.status = "active" AND p.is_bestseller = 1
     ORDER BY p.review_count DESC LIMIT 8'
);
$stmt->execute();
$bestsellers = $stmt->fetchAll();

$categoryTiles = [
    ['slug' => 'skincare', 'name' => 'Skincare', 'desc' => 'Cleanser, serum, moisturizer and more.', 'image' => 'assets/images/skincare.png'],
    ['slug' => 'makeup', 'name' => 'Makeup', 'desc' => 'Complexion, lips, eyes and more.', 'image' => 'assets/images/makeup.png'],
    ['slug' => 'body-care', 'name' => 'Body Care', 'desc' => 'Nourish, hydrate and soften.', 'image' => 'assets/images/bodycare.png'],
    ['slug' => 'glow-essentials', 'name' => 'Glow Essentials', 'desc' => 'Our most-loved beauty products.', 'image' => 'assets/images/glowessentials.png'],
];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-content">
    <p class="eyebrow">MADIXX</p>
    <h1>Beauty, Refined.</h1>
    <p>Discover skincare and cosmetics designed to enhance your natural glow.</p>
    <div class="hero-actions">
      <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Shop Collection</a>
      <a href="<?= e(base_url('about.php')) ?>" class="btn btn-outline">Discover MADIXX</a>
    </div>
  </div>
</section>

<section class="section fade-in">
  <div class="container">
    <div class="section-heading text-center">
      <p class="eyebrow">Shop By Category</p>
      <h2>Find Your Glow</h2>
      <p>Curated edits for every step of your beauty ritual.</p>
    </div>
    <div class="category-grid">
      <?php foreach ($categoryTiles as $tile): ?>
      <a href="<?= e(base_url('shop.php?category=' . $tile['slug'])) ?>" class="category-card">
        <img src="<?= e(base_url($tile['image'])) ?>" alt="<?= e($tile['name']) ?>" loading="lazy">
        <div class="category-card-overlay">
          <h3><?= e($tile['name']) ?></h3>
          <p><?= e($tile['desc']) ?></p>
          <span>Shop Now</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section fade-in" style="background:var(--color-cream);">
  <div class="container">
    <div class="section-heading text-center">
      <p class="eyebrow">Customer Favorites</p>
      <h2>The MADIXX Edit</h2>
      <p>Our most-loved formulas, chosen by you.</p>
    </div>
    <div class="product-grid">
      <?php foreach ($bestsellers as $product): ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top:48px;">
      <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-outline">View All Products</a>
    </div>
  </div>
</section>

<section class="section fade-in">
  <div class="container editorial-split">
    <img src="<?= e(base_url('assets/images/placeholder-category.jpg')) ?>" alt="The MADIXX ritual" loading="lazy">
    <div>
      <p class="eyebrow">Our Philosophy</p>
      <h2>Skincare As Self-Respect</h2>
      <p style="color:var(--text-muted);margin-bottom:1.6em;">Every MADIXX formula is built on clinically-proven actives, gentle textures and a quiet kind of luxury. We believe your routine should feel like a ritual, not a chore.</p>
      <a href="<?= e(base_url('about.php')) ?>" class="btn btn-primary">Discover Our Story</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
