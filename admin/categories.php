<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete') {
        $catId = (int) $_POST['category_id'];
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
        $countStmt->execute(['id' => $catId]);
        if ((int) $countStmt->fetchColumn() > 0) {
            flash('error', 'Cannot delete a category that still has products. Move or delete its products first.');
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = :id')->execute(['id' => $catId]);
            flash('success', 'Category deleted.');
        }
        redirect('admin/categories.php');
    }

    if ($action === 'save') {
        $catId = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($name === '') {
            $errors[] = 'Category name is required.';
        }

        if (!$errors) {
            $slug = unique_slug('categories', $name, $catId ?: null);
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $image = upload_image($_FILES['image'], 'products');
            }

            if ($catId > 0) {
                if ($image) {
                    $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description, image = :image WHERE id = :id')
                        ->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'image' => $image, 'id' => $catId]);
                } else {
                    $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id')
                        ->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'id' => $catId]);
                }
                flash('success', 'Category updated.');
            } else {
                $pdo->prepare('INSERT INTO categories (name, slug, description, image) VALUES (:name, :slug, :description, :image)')
                    ->execute(['name' => $name, 'slug' => $slug, 'description' => $description, 'image' => $image ?: 'assets/images/placeholder-category.jpg']);
                flash('success', 'Category created.');
            }
            redirect('admin/categories.php');
        }
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editing = $stmt->fetch();
}

$categories = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.name ASC'
)->fetchAll();

$pageTitle = 'Categories';
$activeAdminPage = 'categories';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-card">
  <div class="admin-card-head"><h3 style="margin:0;"><?= $editing ? 'Edit Category' : 'Add Category' ?></h3></div>

  <?php if ($errors): ?>
  <div class="flash flash-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="category_id" value="<?= (int) ($editing['id'] ?? 0) ?>">
    <div class="form-row">
      <div class="form-group"><label>Category Name</label><input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description"><?= e($editing['description'] ?? '') ?></textarea></div>
    <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Category' : 'Add Category' ?></button>
    <?php if ($editing): ?><a href="<?= e(base_url('admin/categories.php')) ?>" class="btn btn-outline">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3 style="margin:0;">All Categories</h3></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th></th><th>Name</th><th>Slug</th><th>Products</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td><img src="<?= e(base_url($cat['image'])) ?>" class="table-thumb" alt=""></td>
          <td><?= e($cat['name']) ?></td>
          <td><?= e($cat['slug']) ?></td>
          <td><?= (int) $cat['product_count'] ?></td>
          <td>
            <div style="display:flex;gap:8px;">
              <a href="<?= e(base_url('admin/categories.php?edit=' . (int) $cat['id'])) ?>" class="btn btn-outline btn-sm">Edit</a>
              <form method="post" data-confirm="Delete this category?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
