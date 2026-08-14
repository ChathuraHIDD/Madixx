<?php

declare(strict_types=1);

/**
 * Expects (all optional) before include:
 *   $pageTitle        string
 *   $metaDescription  string
 *   $canonicalPath    string  e.g. 'shop.php'
 *   $bodyClass        string
 *   $ogImage          string
 */
$pageTitle ??= 'MADIXX — Sunglasses & Eyewear, Refined.';
$metaDescription ??= 'MADIXX is a premium eyewear destination — sunglasses, prescription-ready spectacles and eyewear accessories, designed to see and be seen.';
$canonicalPath ??= trim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/');
$bodyClass ??= '';
$ogImage ??= base_url('assets/images/hero.png');

$cartCount = get_cart_count();
$user = current_user();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<link rel="canonical" href="<?= e(base_url($canonicalPath)) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:site_name" content="MADIXX">
<link rel="icon" type="image/png" href="<?= e(base_url('assets/images/logo.png')) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<script>
  window.MADIXX = {
    baseUrl: <?= json_encode(BASE_URL) ?>,
    csrfToken: <?= json_encode(csrf_token()) ?>,
    isLoggedIn: <?= $user ? 'true' : 'false' ?>
  };
</script>

<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

<div class="announcement-bar">
  <p>FREE SHIPPING ON ORDERS OVER $75 &nbsp;•&nbsp; UV400 PROTECTION ON EVERY PAIR</p>
</div>

<header class="site-header" id="site-header">
  <div class="header-inner">
    <button type="button" class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
      <span></span><span></span><span></span>
    </button>

    <a href="<?= e(base_url('index.php')) ?>" class="logo"><img src="<?= e(base_url('assets/images/logo.png')) ?>" alt="MADIXX" class="logo-img"></a>

    <nav class="main-nav" aria-label="Primary">
      <ul>
        <li><a href="<?= e(base_url('index.php')) ?>">Home</a></li>
        <li><a href="<?= e(base_url('shop.php')) ?>">Shop</a></li>
        <li><a href="<?= e(base_url('sunglasses.php')) ?>">Sunglasses</a></li>
        <li><a href="<?= e(base_url('spectacles.php')) ?>">Spectacles</a></li>
        <li><a href="<?= e(base_url('accessories.php')) ?>">Accessories</a></li>
        <li><a href="<?= e(base_url('collections.php')) ?>">Collections</a></li>
        <li><a href="<?= e(base_url('journal.php')) ?>">Journal</a></li>
        <li><a href="<?= e(base_url('about.php')) ?>">About</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <button type="button" class="icon-btn" id="searchToggle" aria-label="Search" aria-expanded="false" aria-controls="searchBar">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <a href="<?= e(base_url($user ? 'account.php' : 'login.php')) ?>" class="icon-btn" aria-label="Account">
        <i class="fa-regular fa-user"></i>
      </a>
      <a href="<?= e(base_url('wishlist.php')) ?>" class="icon-btn" aria-label="Wishlist">
        <i class="fa-regular fa-heart"></i>
      </a>
      <button type="button" class="icon-btn cart-toggle" id="cartToggle" aria-label="Cart" aria-expanded="false" aria-controls="cartDrawer">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="cart-count" id="cartCount"><?= (int) $cartCount ?></span>
      </button>
    </div>
  </div>

  <div class="search-bar" id="searchBar">
    <form action="<?= e(base_url('search.php')) ?>" method="get" class="search-form">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" name="q" placeholder="Search for sunglasses, spectacles, accessories…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search products">
      <button type="submit">Search</button>
    </form>
  </div>
</header>

<nav class="mobile-nav" id="mobileNav" aria-label="Mobile">
  <ul>
    <li><a href="<?= e(base_url('index.php')) ?>">Home</a></li>
    <li><a href="<?= e(base_url('shop.php')) ?>">Shop</a></li>
    <li><a href="<?= e(base_url('skincare.php')) ?>">Skincare</a></li>
    <li><a href="<?= e(base_url('makeup.php')) ?>">Makeup</a></li>
    <li><a href="<?= e(base_url('body.php')) ?>">Body</a></li>
    <li><a href="<?= e(base_url('collections.php')) ?>">Collections</a></li>
    <li><a href="<?= e(base_url('journal.php')) ?>">Journal</a></li>
    <li><a href="<?= e(base_url('about.php')) ?>">About</a></li>
    <li><a href="<?= e(base_url('quiz.php')) ?>">Beauty Quiz</a></li>
    <li><a href="<?= e(base_url('contact.php')) ?>">Contact</a></li>
    <li class="mobile-nav-divider"></li>
    <li><a href="<?= e(base_url($user ? 'account.php' : 'login.php')) ?>"><?= $user ? 'My Account' : 'Login / Register' ?></a></li>
    <li><a href="<?= e(base_url('wishlist.php')) ?>">Wishlist</a></li>
  </ul>
</nav>
<div class="nav-overlay" id="navOverlay"></div>

<aside class="cart-drawer" id="cartDrawer" aria-label="Shopping cart" aria-hidden="true">
  <div class="cart-drawer-header">
    <h2>Your Bag</h2>
    <button type="button" class="drawer-close" id="cartDrawerClose" aria-label="Close cart">&times;</button>
  </div>
  <div class="cart-drawer-body" id="cartDrawerBody">
    <p class="cart-drawer-loading">Loading…</p>
  </div>
  <div class="cart-drawer-footer">
    <div class="cart-drawer-subtotal">
      <span>Subtotal</span>
      <strong id="cartDrawerSubtotal">$0.00</strong>
    </div>
    <a href="<?= e(base_url('cart.php')) ?>" class="btn btn-outline btn-block">View Bag</a>
    <a href="<?= e(base_url('checkout.php')) ?>" class="btn btn-primary btn-block">Checkout</a>
  </div>
</aside>

<div class="modal-overlay" id="quickViewOverlay">
  <div class="modal-box">
    <button type="button" class="modal-close" id="quickViewClose" aria-label="Close">&times;</button>
  </div>
</div>

<main>
<?php foreach (get_flashes() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
