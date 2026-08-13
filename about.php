<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'About Us — Glowelle';
$metaDescription = 'Learn about Glowelle — premium skincare and cosmetics designed to enhance your natural glow.';
$canonicalPath = 'about.php';

require __DIR__ . '/includes/header.php';
?>

<section class="hero" style="min-height:420px;">
  <div class="hero-content" style="margin:0 auto;text-align:center;max-width:640px;">
    <p class="eyebrow">Our Story</p>
    <h1>Beauty, Refined.</h1>
    <p style="margin:0 auto;">Glowelle was founded on a simple belief: that skincare should feel like a ritual, not a routine.</p>
  </div>
</section>

<section class="section fade-in">
  <div class="container editorial-split">
    <div>
      <p class="eyebrow">Our Philosophy</p>
      <h2>Skincare As Self-Respect</h2>
      <p style="color:var(--text-muted);margin-bottom:1.4em;">Every Glowelle formula begins with a question: does this deserve a place in someone's daily ritual? We start with clinically-proven actives, refine every texture until it feels effortless, and never compromise on what goes into the bottle.</p>
      <p style="color:var(--text-muted);">The result is a collection of skincare and cosmetics that performs as beautifully as it feels — quietly luxurious, thoughtfully formulated, and made to be lived in.</p>
    </div>
    <img src="<?= e(base_url('assets/images/placeholder-category.jpg')) ?>" alt="The Glowelle studio" loading="lazy">
  </div>
</section>

<section class="section fade-in" style="background:var(--color-cream);">
  <div class="container">
    <div class="section-heading text-center">
      <p class="eyebrow">What We Stand For</p>
      <h2>Our Values</h2>
    </div>
    <div class="value-grid">
      <div>
        <i class="fa-solid fa-leaf"></i>
        <h3 style="font-size:1.1rem;">Clean Formulas</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">Every ingredient is chosen with intention — no fillers, no shortcuts.</p>
      </div>
      <div>
        <i class="fa-solid fa-paw"></i>
        <h3 style="font-size:1.1rem;">Cruelty-Free</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">Glowelle is never tested on animals, at any stage of development.</p>
      </div>
      <div>
        <i class="fa-solid fa-heart"></i>
        <h3 style="font-size:1.1rem;">Made With Care</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">From formulation to packaging, every detail is considered.</p>
      </div>
    </div>
  </div>
</section>

<section class="section fade-in text-center">
  <div class="container" style="max-width:600px;">
    <p class="eyebrow">Ready to Glow?</p>
    <h2 style="margin-bottom:24px;">Discover Your Ritual</h2>
    <div class="hero-actions" style="justify-content:center;">
      <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Shop Collection</a>
      <a href="<?= e(base_url('quiz.php')) ?>" class="btn btn-outline">Take the Beauty Quiz</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
