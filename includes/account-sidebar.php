<?php

declare(strict_types=1);

/** Expects $activeAccountPage (string) set before include. */
$accountLinks = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-gauge', 'url' => 'account.php'],
    'profile'   => ['label' => 'My Profile', 'icon' => 'fa-user', 'url' => 'account-profile.php'],
    'orders'    => ['label' => 'My Orders', 'icon' => 'fa-box', 'url' => 'orders.php'],
    'wishlist'  => ['label' => 'Wishlist', 'icon' => 'fa-heart', 'url' => 'wishlist.php'],
    'addresses' => ['label' => 'Addresses', 'icon' => 'fa-location-dot', 'url' => 'account-addresses.php'],
    'password'  => ['label' => 'Change Password', 'icon' => 'fa-lock', 'url' => 'account-password.php'],
];
?>
<aside class="account-sidebar">
  <?php foreach ($accountLinks as $key => $link): ?>
  <a href="<?= e(base_url($link['url'])) ?>" class="<?= ($activeAccountPage ?? '') === $key ? 'active' : '' ?>">
    <i class="fa-solid <?= e($link['icon']) ?>"></i> <?= e($link['label']) ?>
  </a>
  <?php endforeach; ?>
  <a href="<?= e(base_url('logout.php')) ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
</aside>
