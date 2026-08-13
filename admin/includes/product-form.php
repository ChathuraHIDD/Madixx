<?php

declare(strict_types=1);

/**
 * Shared add/edit product form.
 * Expects $product (array or null for "add"), $categories, $errors before include.
 */
$p = $product ?? [];
$val = static fn (string $key, $default = '') => e((string) ($p[$key] ?? $default));
?>
<?php if ($errors): ?>
<div class="flash flash-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="admin-card">
    <h3>Basic Information</h3>
    <div class="form-row">
      <div class="form-group"><label>Product Name</label><input type="text" name="name" required value="<?= $val('name') ?>"></div>
      <div class="form-group"><label>SKU</label><input type="text" name="sku" required value="<?= $val('sku') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Category</label>
        <select name="category_id" required>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= (string) ($p['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Brand</label><input type="text" name="brand" value="<?= $val('brand', 'Glowelle') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Product Type</label><input type="text" name="product_type" placeholder="Serum, Cleanser, Lipstick…" value="<?= $val('product_type') ?>"></div>
      <div class="form-group"><label>Skin Type</label><input type="text" name="skin_type" placeholder="Dry, Oily, All Skin Types…" value="<?= $val('skin_type') ?>"></div>
    </div>
    <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= $val('short_description') ?>"></div>
    <div class="form-group"><label>Full Description</label><textarea name="description"><?= $val('description') ?></textarea></div>
  </div>

  <div class="admin-card">
    <h3>Pricing &amp; Stock</h3>
    <div class="form-row-3">
      <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" min="0" name="price" required value="<?= $val('price') ?>"></div>
      <div class="form-group"><label>Sale Price ($)</label><input type="number" step="0.01" min="0" name="sale_price" value="<?= $val('sale_price') ?>"></div>
      <div class="form-group"><label>Stock Quantity</label><input type="number" min="0" name="stock" required value="<?= $val('stock', '0') ?>"></div>
    </div>
  </div>

  <div class="admin-card">
    <h3>Product Details</h3>
    <div class="form-group"><label>Ingredients (comma-separated)</label><textarea name="ingredients"><?= $val('ingredients') ?></textarea></div>
    <div class="form-group"><label>Benefits (one per line)</label><textarea name="benefits"><?= $val('benefits') ?></textarea></div>
    <div class="form-group"><label>How To Use</label><textarea name="how_to_use"><?= $val('how_to_use') ?></textarea></div>
  </div>

  <div class="admin-card">
    <h3>Images</h3>
    <div class="form-group">
      <label>Main Image</label>
      <?php if (!empty($p['image'])): ?><img src="<?= e(base_url($p['image'])) ?>" class="table-thumb" style="width:80px;height:80px;margin-bottom:10px;" alt=""><?php endif; ?>
      <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="form-group">
      <label>Additional Gallery Images</label>
      <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
    </div>
    <?php if (!empty($galleryImages)): ?>
    <div class="form-group">
      <label>Existing Gallery Images</label>
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <?php foreach ($galleryImages as $img): ?>
        <div style="text-align:center;">
          <img src="<?= e(base_url($img['image'])) ?>" class="table-thumb" style="width:70px;height:70px;margin-bottom:6px;" alt="">
          <form method="post" style="margin:0;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_image">
            <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
            <button type="submit" class="btn btn-outline btn-sm" style="padding:3px 8px;">Remove</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-card">
    <h3>Visibility</h3>
    <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1" <?= !empty($p['is_featured']) ? 'checked' : '' ?>> Mark as Featured</label>
    <label class="checkbox-row"><input type="checkbox" name="is_bestseller" value="1" <?= !empty($p['is_bestseller']) ? 'checked' : '' ?>> Mark as Bestseller</label>
    <label class="checkbox-row"><input type="checkbox" name="is_new" value="1" <?= !empty($p['is_new']) ? 'checked' : '' ?>> Mark as New Arrival</label>
    <div class="form-group" style="margin-top:14px;max-width:240px;">
      <label>Status</label>
      <select name="status">
        <option value="active" <?= ($p['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="draft" <?= ($p['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
      </select>
    </div>
  </div>

  <button type="submit" name="action" value="save" class="btn btn-primary">Save Product</button>
  <a href="<?= e(base_url('admin/products.php')) ?>" class="btn btn-outline">Cancel</a>
</form>
