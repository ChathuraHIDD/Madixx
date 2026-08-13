<?php

declare(strict_types=1);

/** Expects $post (array or null), $errors before include. */
$post ??= [];
$categories = ['Skincare Tips', 'Makeup Tips', 'Beauty Routines', 'Ingredients', 'Glowelle News'];
$val = static fn (string $key, $default = '') => e((string) ($post[$key] ?? $default));
?>
<?php if ($errors): ?>
<div class="flash flash-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="admin-card">
    <div class="form-group"><label>Title</label><input type="text" name="title" required value="<?= $val('title') ?>"></div>
    <div class="form-row">
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat) ?>" <?= ($post['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Author</label><input type="text" name="author" value="<?= $val('author', 'Glowelle Team') ?>"></div>
    </div>
    <div class="form-group"><label>Excerpt</label><textarea name="excerpt"><?= $val('excerpt') ?></textarea></div>
    <div class="form-group"><label>Content (HTML supported, e.g. &lt;p&gt; paragraphs)</label><textarea name="content" required style="min-height:260px;"><?= $val('content') ?></textarea></div>
  </div>

  <div class="admin-card">
    <div class="form-group">
      <label>Featured Image</label>
      <?php if (!empty($post['featured_image'])): ?><img src="<?= e(base_url($post['featured_image'])) ?>" class="table-thumb" style="width:100px;height:70px;margin-bottom:10px;" alt=""><?php endif; ?>
      <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="form-group" style="max-width:240px;">
      <label>Status</label>
      <select name="status">
        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
      </select>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">Save Post</button>
  <a href="<?= e(base_url('admin/blog.php')) ?>" class="btn btn-outline">Cancel</a>
</form>
