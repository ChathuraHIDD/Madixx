<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$faceShape = trim((string) ($_POST['face_shape'] ?? ''));
$useCase = trim((string) ($_POST['use_case'] ?? ''));
$style = trim((string) ($_POST['style'] ?? ''));

$faceShapeTypes = [
    'Round'  => ['Square', 'Geometric', 'Browline'],
    'Oval'   => ['Aviator', 'Square', 'Cat-Eye', 'Round'],
    'Square' => ['Round', 'Oval', 'Cat-Eye'],
    'Heart'  => ['Cat-Eye', 'Round', 'Oval'],
];
$styleTypes = [
    'Classic' => ['Aviator', 'Round', 'Clubmaster'],
    'Bold'    => ['Square', 'Shield', 'Geometric', 'Cat-Eye'],
    'Minimal' => ['Rimless', 'Oval', 'Square'],
    'Vintage' => ['Round', 'Browline', 'Cat-Eye', 'Oval'],
];
$useCaseCategory = [
    'Sun Protection'      => 'sunglasses',
    'Prescription Vision' => 'spectacles',
];

$types = array_values(array_unique(array_merge($faceShapeTypes[$faceShape] ?? [], $styleTypes[$style] ?? [])));
$categorySlug = $useCaseCategory[$useCase] ?? null;

function run_recommendation_query(array $types, ?string $categorySlug, bool $withTypes): array
{
    $where = ['p.status = "active"'];
    $params = [];

    if ($withTypes && $types) {
        $placeholders = [];
        foreach ($types as $i => $type) {
            $key = 'type' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $type;
        }
        $where[] = 'p.product_type IN (' . implode(',', $placeholders) . ')';
    }

    if ($categorySlug !== null) {
        $where[] = 'c.slug = :category_slug';
        $params['category_slug'] = $categorySlug;
    }

    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON c.id = p.category_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY p.rating DESC, p.review_count DESC LIMIT 6';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

$products = run_recommendation_query($types, $categorySlug, true);

if (count($products) < 3) {
    $products = run_recommendation_query([], $categorySlug, false);
}

if (count($products) < 3) {
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p JOIN categories c ON c.id = p.category_id
         WHERE p.status = "active" ORDER BY p.is_bestseller DESC, p.rating DESC LIMIT 6'
    );
    $stmt->execute();
    $products = $stmt->fetchAll();
}

$wishlistIds = get_wishlist_product_ids();

ob_start();
foreach ($products as $product) {
    include __DIR__ . '/../includes/product-card.php';
}
$html = ob_get_clean();

echo json_encode(['success' => true, 'html' => $html, 'count' => count($products)]);
