<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$productId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Product not found.');
    redirect('admin/products.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete_image') {
        $pdo->prepare('DELETE FROM product_images WHERE id = :id AND product_id = :pid')
            ->execute(['id' => (int) $_POST['image_id'], 'pid' => $productId]);
        flash('success', 'Image removed.');
        redirect('admin/edit-product.php?id=' . $productId);
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $sku = trim((string) ($_POST['sku'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $salePrice = $_POST['sale_price'] !== '' ? (float) $_POST['sale_price'] : null;
    $stock = (int) ($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = 'Product name is required.';
    if ($sku === '') $errors[] = 'SKU is required.';
    if ($categoryId < 1) $errors[] = 'Please select a category.';
    if ($price <= 0) $errors[] = 'Price must be greater than 0.';

    if (!$errors) {
        $dupStmt = $pdo->prepare('SELECT id FROM products WHERE sku = :sku AND id != :id');
        $dupStmt->execute(['sku' => $sku, 'id' => $productId]);
        if ($dupStmt->fetch()) {
            $errors[] = 'Another product already uses this SKU.';
        }
    }

    if (!$errors) {
        $mainImage = $product['image'];
        if (!empty($_FILES['main_image']['name'])) {
            $uploaded = upload_image($_FILES['main_image'], 'products');
            if ($uploaded) {
                $mainImage = $uploaded;
            } else {
                $errors[] = 'Main image upload failed. Please use a JPG, PNG, WEBP or GIF under 5MB.';
            }
        }
    }

    if (!$errors) {
        $slug = $name !== $product['name'] ? unique_slug('products', $name, $productId) : $product['slug'];

        $pdo->prepare(
            'UPDATE products SET category_id = :category_id, name = :name, slug = :slug, sku = :sku,
             description = :description, short_description = :short_description, brand = :brand,
             product_type = :product_type, skin_type = :skin_type, price = :price, sale_price = :sale_price,
             stock = :stock, image = :image, ingredients = :ingredients, benefits = :benefits, how_to_use = :how_to_use,
             is_featured = :is_featured, is_bestseller = :is_bestseller, is_new = :is_new, status = :status
             WHERE id = :id'
        )->execute([
            'category_id'       => $categoryId,
            'name'              => $name,
            'slug'              => $slug,
            'sku'               => $sku,
            'description'       => trim((string) ($_POST['description'] ?? '')),
            'short_description' => trim((string) ($_POST['short_description'] ?? '')),
            'brand'             => trim((string) ($_POST['brand'] ?? 'MADIXX')),
            'product_type'      => trim((string) ($_POST['product_type'] ?? '')) ?: null,
            'skin_type'         => trim((string) ($_POST['skin_type'] ?? '')) ?: null,
            'price'             => $price,
            'sale_price'        => $salePrice,
            'stock'             => $stock,
            'image'             => $mainImage,
            'ingredients'       => trim((string) ($_POST['ingredients'] ?? '')),
            'benefits'          => trim((string) ($_POST['benefits'] ?? '')),
            'how_to_use'        => trim((string) ($_POST['how_to_use'] ?? '')),
            'is_featured'       => !empty($_POST['is_featured']) ? 1 : 0,
            'is_bestseller'     => !empty($_POST['is_bestseller']) ? 1 : 0,
            'is_new'            => !empty($_POST['is_new']) ? 1 : 0,
            'status'            => in_array($_POST['status'] ?? '', ['active', 'draft'], true) ? $_POST['status'] : 'active',
            'id'                => $productId,
        ]);

        if (!empty($_FILES['gallery_images']['name'][0])) {
            $count = count($_FILES['gallery_images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'error'    => $_FILES['gallery_images']['error'][$i],
                    'size'     => $_FILES['gallery_images']['size'][$i],
                ];
                $path = upload_image($file, 'products');
                if ($path) {
                    $pdo->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (:pid, :img, :sort)')
                        ->execute(['pid' => $productId, 'img' => $path, 'sort' => $i + 1]);
                }
            }
        }

        flash('success', 'Product updated.');
        redirect('admin/edit-product.php?id=' . $productId);
    }

    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();
}

$galleryStmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC');
$galleryStmt->execute(['id' => $productId]);
$galleryImages = $galleryStmt->fetchAll();

$pageTitle = 'Edit Product';
$activeAdminPage = 'products';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/product-form.php';
require __DIR__ . '/includes/admin-footer.php';
