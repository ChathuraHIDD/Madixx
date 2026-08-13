<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$skinType = trim((string) ($_POST['skin_type'] ?? ''));
$concern = trim((string) ($_POST['concern'] ?? ''));
$goal = trim((string) ($_POST['goal'] ?? ''));

$concernTypes = [
    'Acne'        => ['Cleanser', 'Toner'],
    'Dryness'     => ['Moisturizer', 'Body Butter', 'Body Oil'],
    'Dullness'    => ['Serum'],
    'Aging'       => ['Night Cream', 'Serum'],
    'Sensitivity' => ['Moisturizer', 'Cleanser'],
];
$goalTypes = [
    'Hydration'    => ['Moisturizer', 'Toner', 'Body Butter'],
    'Brightening'  => ['Serum'],
    'Anti-aging'   => ['Night Cream', 'Serum'],
    'Skin barrier' => ['Moisturizer'],
    'Glow'         => ['Serum', 'Body Oil'],
];

$types = array_values(array_unique(array_merge($concernTypes[$concern] ?? [], $goalTypes[$goal] ?? [])));

function run_recommendation_query(array $types, string $skinType, bool $withTypes): array
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

    if ($skinType !== '') {
        $where[] = '(p.skin_type LIKE :skin OR p.skin_type LIKE "%All Skin Types%")';
        $params['skin'] = '%' . $skinType . '%';
    }

    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON c.id = p.category_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY p.rating DESC, p.review_count DESC LIMIT 6';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

$products = run_recommendation_query($types, $skinType, true);

if (count($products) < 3) {
    $products = run_recommendation_query([], $skinType, false);
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
