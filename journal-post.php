<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$slug = trim((string) ($_GET['slug'] ?? ''));

$stmt = db()->prepare('SELECT * FROM blog_posts WHERE slug = :slug AND status = "published" LIMIT 1');
$stmt->execute(['slug' => $slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Article Not Found — MADIXX';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><i class="fa-regular fa-face-frown"></i><h2>Article not found</h2><a href="' . e(base_url('journal.php')) . '" class="btn btn-primary">Back to Journal</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$relatedStmt = db()->prepare('SELECT * FROM blog_posts WHERE category = :cat AND id != :id AND status = "published" ORDER BY created_at DESC LIMIT 3');
$relatedStmt->execute(['cat' => $post['category'], 'id' => $post['id']]);
$related = $relatedStmt->fetchAll();

$pageTitle = $post['title'] . ' — MADIXX Journal';
$metaDescription = $post['excerpt'] ?: $post['title'];
$canonicalPath = 'journal-post.php?slug=' . $post['slug'];
$ogImage = base_url($post['featured_image']);

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([
      ['label' => 'Journal', 'url' => base_url('journal.php')],
      ['label' => $post['title'], 'url' => null],
  ]) ?>

  <div class="text-center" style="max-width:720px;margin:0 auto 32px;">
    <p class="post-category" style="text-align:center;"><?= e($post['category']) ?></p>
    <h1 style="font-size:2.2rem;"><?= e($post['title']) ?></h1>
    <p class="post-meta">By <?= e($post['author']) ?> · <?= e(date('F j, Y', strtotime($post['created_at']))) ?></p>
  </div>

  <img src="<?= e(base_url($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" style="width:100%;max-width:900px;margin:0 auto 40px;border-radius:var(--radius-md);aspect-ratio:16/9;object-fit:cover;display:block;">

  <div class="post-body">
    <?= $post['content'] ?>
  </div>

  <?php if ($related): ?>
  <div style="margin-top:80px;">
    <h3 class="text-center" style="margin-bottom:32px;">More From <?= e($post['category']) ?></h3>
    <div class="blog-grid">
      <?php foreach ($related as $r): ?>
      <a href="<?= e(base_url('journal-post.php?slug=' . $r['slug'])) ?>" class="blog-card">
        <img src="<?= e(base_url($r['featured_image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
        <p class="post-category"><?= e($r['category']) ?></p>
        <h3><?= e($r['title']) ?></h3>
        <p><?= e($r['excerpt']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
