<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/admin-auth.php';

$pdo = db();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$errors = [];
$product = [];
$galleryImages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $product = [
        'name'              => trim((string) ($_POST['name'] ?? '')),
        'sku'               => trim((string) ($_POST['sku'] ?? '')),
        'category_id'       => (int) ($_POST['category_id'] ?? 0),
        'brand'             => trim((string) ($_POST['brand'] ?? 'MADIXX')),
        'product_type'      => trim((string) ($_POST['product_type'] ?? '')),
        'skin_type'         => trim((string) ($_POST['skin_type'] ?? '')),
        'short_description' => trim((string) ($_POST['short_description'] ?? '')),
        'description'       => trim((string) ($_POST['description'] ?? '')),
        'price'             => (float) ($_POST['price'] ?? 0),
        'sale_price'        => $_POST['sale_price'] !== '' ? (float) $_POST['sale_price'] : null,
        'stock'             => (int) ($_POST['stock'] ?? 0),
        'ingredients'       => trim((string) ($_POST['ingredients'] ?? '')),
        'benefits'          => trim((string) ($_POST['benefits'] ?? '')),
        'how_to_use'        => trim((string) ($_POST['how_to_use'] ?? '')),
        'is_featured'       => !empty($_POST['is_featured']) ? 1 : 0,
        'is_bestseller'     => !empty($_POST['is_bestseller']) ? 1 : 0,
        'is_new'            => !empty($_POST['is_new']) ? 1 : 0,
        'status'            => in_array($_POST['status'] ?? '', ['active', 'draft'], true) ? $_POST['status'] : 'active',
    ];

    if ($product['name'] === '') $errors[] = 'Product name is required.';
    if ($product['sku'] === '') $errors[] = 'SKU is required.';
    if ($product['category_id'] < 1) $errors[] = 'Please select a category.';
    if ($product['price'] <= 0) $errors[] = 'Price must be greater than 0.';

    if (!$errors) {
        $dupStmt = $pdo->prepare('SELECT id FROM products WHERE sku = :sku');
        $dupStmt->execute(['sku' => $product['sku']]);
        if ($dupStmt->fetch()) {
            $errors[] = 'A product with this SKU already exists.';
        }
    }

    if (!$errors) {
        $mainImage = 'assets/images/logo.png';
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
        $slug = unique_slug('products', $product['name']);

        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, name, slug, sku, description, short_description, brand, product_type, skin_type, price, sale_price, stock, image, ingredients, benefits, how_to_use, is_featured, is_bestseller, is_new, status)
             VALUES (:category_id, :name, :slug, :sku, :description, :short_description, :brand, :product_type, :skin_type, :price, :sale_price, :stock, :image, :ingredients, :benefits, :how_to_use, :is_featured, :is_bestseller, :is_new, :status)'
        );
        $stmt->execute([
            'category_id'       => $product['category_id'],
            'name'              => $product['name'],
            'slug'              => $slug,
            'sku'               => $product['sku'],
            'description'       => $product['description'],
            'short_description' => $product['short_description'],
            'brand'             => $product['brand'],
            'product_type'      => $product['product_type'] ?: null,
            'skin_type'         => $product['skin_type'] ?: null,
            'price'             => $product['price'],
            'sale_price'        => $product['sale_price'],
            'stock'             => $product['stock'],
            'image'             => $mainImage,
            'ingredients'       => $product['ingredients'],
            'benefits'          => $product['benefits'],
            'how_to_use'        => $product['how_to_use'],
            'is_featured'       => $product['is_featured'],
            'is_bestseller'     => $product['is_bestseller'],
            'is_new'            => $product['is_new'],
            'status'            => $product['status'],
        ]);

        $newId = (int) $pdo->lastInsertId();

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
                        ->execute(['pid' => $newId, 'img' => $path, 'sort' => $i + 1]);
                }
            }
        }

        flash('success', 'Product "' . $product['name'] . '" has been created.');
        redirect('admin/edit-product.php?id=' . $newId);
    }
}

$pageTitle = 'Add Product';
$activeAdminPage = 'products';
require __DIR__ . '/includes/admin-header.php';
require __DIR__ . '/includes/product-form.php';
require __DIR__ . '/includes/admin-footer.php';
