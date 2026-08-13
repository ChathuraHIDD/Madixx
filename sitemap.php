<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/xml; charset=utf-8');

$staticPages = [
    'index.php', 'shop.php', 'skincare.php', 'makeup.php', 'body.php', 'collections.php',
    'about.php', 'contact.php', 'quiz.php', 'journal.php', 'faq.php', 'login.php', 'register.php',
];

$products = db()->query("SELECT slug, updated_at FROM products WHERE status = 'active'")->fetchAll();
$posts = db()->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'published'")->fetchAll();
$categories = db()->query('SELECT slug FROM categories')->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php foreach ($staticPages as $page): ?>
  <url><loc><?= e(base_url($page)) ?></loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
  <?php endforeach; ?>
  <?php foreach ($categories as $cat): ?>
  <url><loc><?= e(base_url('shop.php?category=' . $cat['slug'])) ?></loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
  <?php endforeach; ?>
  <?php foreach ($products as $p): ?>
  <url><loc><?= e(base_url('product.php?slug=' . $p['slug'])) ?></loc><lastmod><?= e(date('Y-m-d', strtotime($p['updated_at']))) ?></lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <?php endforeach; ?>
  <?php foreach ($posts as $post): ?>
  <url><loc><?= e(base_url('journal-post.php?slug=' . $post['slug'])) ?></loc><lastmod><?= e(date('Y-m-d', strtotime($post['updated_at']))) ?></lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>
  <?php endforeach; ?>
</urlset>
