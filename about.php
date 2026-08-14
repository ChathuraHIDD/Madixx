<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$pageTitle = 'About Us — MADIXX';
$metaDescription = 'Learn about MADIXX — sunglasses, spectacles and eyewear accessories designed to see and be seen.';
$canonicalPath = 'about.php';

require __DIR__ . '/includes/header.php';
?>

<section class="hero" style="min-height:420px;">
  <div class="hero-content" style="margin:0 auto;text-align:center;max-width:640px;">
    <p class="eyebrow">Our Story</p>
    <h1>See Clearly. Stand Out.</h1>
    <p style="margin:0 auto;">MADIXX was founded on a simple belief: that eyewear should feel like a natural extension of you, not an accessory you settle for.</p>
  </div>
</section>

<section class="section fade-in">
  <div class="container editorial-split">
    <div>
      <p class="eyebrow">Our Philosophy</p>
      <h2>Eyewear As Self-Expression</h2>
      <p style="color:var(--text-muted);margin-bottom:1.4em;">Every MADIXX frame begins with a question: does this deserve a place on someone's face, every single day? We start with durable materials, refine every fit until it feels effortless, and never compromise on lens quality.</p>
      <p style="color:var(--text-muted);">The result is a collection of sunglasses, spectacles and accessories that performs as beautifully as it looks — quietly confident, precisely made, and built to be worn.</p>
    </div>
    <img src="<?= e(base_url('assets/images/hero.png')) ?>" alt="The MADIXX studio" loading="lazy">
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
        <i class="fa-solid fa-sun"></i>
        <h3 style="font-size:1.1rem;">UV400 Protected</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">Every sunglass lens blocks 100% of UVA and UVB rays — no exceptions.</p>
      </div>
      <div>
        <i class="fa-solid fa-gem"></i>
        <h3 style="font-size:1.1rem;">Precision Crafted</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">From hinge to lens, every frame is built to fit true and last for years.</p>
      </div>
      <div>
        <i class="fa-solid fa-heart"></i>
        <h3 style="font-size:1.1rem;">Made With Care</h3>
        <p style="color:var(--text-muted);font-size:0.92rem;">From frame selection to packaging, every detail is considered.</p>
      </div>
    </div>
  </div>
</section>

<section class="section fade-in text-center">
  <div class="container" style="max-width:600px;">
    <p class="eyebrow">Ready To See Clearly?</p>
    <h2 style="margin-bottom:24px;">Discover Your Frame</h2>
    <div class="hero-actions" style="justify-content:center;">
      <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Shop Collection</a>
      <a href="<?= e(base_url('quiz.php')) ?>" class="btn btn-outline">Take the Frame Finder Quiz</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
