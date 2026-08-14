<?php

declare(strict_types=1);

/** Expects $pageTitle, $activeAdminPage before include. */
$adminUser = current_user();
$navItems = [
    'dashboard'  => ['label' => 'Dashboard', 'icon' => 'fa-gauge', 'url' => 'admin/index.php'],
    'products'   => ['label' => 'Products', 'icon' => 'fa-glasses', 'url' => 'admin/products.php'],
    'categories' => ['label' => 'Categories', 'icon' => 'fa-layer-group', 'url' => 'admin/categories.php'],
    'orders'     => ['label' => 'Orders', 'icon' => 'fa-box', 'url' => 'admin/orders.php'],
    'customers'  => ['label' => 'Customers', 'icon' => 'fa-users', 'url' => 'admin/customers.php'],
    'reviews'    => ['label' => 'Reviews', 'icon' => 'fa-star', 'url' => 'admin/reviews.php'],
    'blog'       => ['label' => 'Blog Posts', 'icon' => 'fa-newspaper', 'url' => 'admin/blog.php'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin') ?> — MADIXX Admin</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
</head>
<body>
<script>
  window.MADIXX = { baseUrl: <?= json_encode(BASE_URL) ?>, csrfToken: <?= json_encode(csrf_token()) ?> };
</script>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= e(base_url('admin/index.php')) ?>" class="logo"><img src="<?= e(base_url('assets/images/logo.png')) ?>" alt="MADIXX" style="height:34px;width:auto;filter:brightness(0) invert(1);"></a>
    <nav class="admin-nav">
      <p class="nav-section">Manage</p>
      <?php foreach ($navItems as $key => $item): ?>
      <a href="<?= e(base_url($item['url'])) ?>" class="<?= ($activeAdminPage ?? '') === $key ? 'active' : '' ?>">
        <i class="fa-solid <?= e($item['icon']) ?>"></i> <?= e($item['label']) ?>
      </a>
      <?php endforeach; ?>
      <p class="nav-section">Account</p>
      <a href="<?= e(base_url('index.php')) ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Store</a>
      <a href="<?= e(base_url('admin/logout.php')) ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <div class="admin-main">
    <div class="admin-topbar">
      <button type="button" id="adminSidebarToggle" class="btn btn-outline btn-sm" style="display:none;"><i class="fa-solid fa-bars"></i></button>
      <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
      <div class="admin-topbar-user">
        <i class="fa-regular fa-circle-user"></i> <?= e($adminUser['name'] ?? 'Admin') ?>
      </div>
    </div>
    <div class="admin-content">
      <?php foreach (get_flashes() as $f): ?>
      <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
