<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'MADIXX — Sunglasses, Spectacles & Eyewear Accessories';
$metaDescription = 'Discover MADIXX: sunglasses, prescription-ready spectacles and eyewear accessories designed to see and be seen. Shop bestselling frames and more.';
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
    ['slug' => 'sunglasses', 'name' => 'Sunglasses', 'desc' => 'UV400 protection in square, round, aviator and shield shapes.', 'image' => 'assets/images/p2.png'],
    ['slug' => 'spectacles', 'name' => 'Spectacles', 'desc' => 'Prescription-ready optical frames for everyday clarity.', 'image' => 'assets/images/p9.png'],
    ['slug' => 'accessories', 'name' => 'Accessories', 'desc' => 'Cases, cleaning cloths, lens spray and chains.', 'image' => 'assets/images/p21.png'],
];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-content">
    <p class="eyebrow">MADIXX</p>
    <h1>See Clearly. Stand Out.</h1>
    <p>Sunglasses, prescription-ready spectacles and eyewear accessories designed to see and be seen.</p>
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
      <h2>Find Your Frame</h2>
      <p>Curated edits for sun, sight and everything in between.</p>
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
      <p>Our most-loved frames, chosen by you.</p>
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
    <img src="<?= e(base_url('assets/images/hero.png')) ?>" alt="The MADIXX studio" loading="lazy">
    <div>
      <p class="eyebrow">Our Philosophy</p>
      <h2>Eyewear As Self-Expression</h2>
      <p style="color:var(--text-muted);margin-bottom:1.6em;">Every MADIXX frame is built on durable materials, precise fit and a quiet kind of confidence. We believe your glasses should feel like an extension of you, not an afterthought.</p>
      <a href="<?= e(base_url('about.php')) ?>" class="btn btn-primary">Discover Our Story</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
