<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

$items = get_cart_items();
$totals = get_cart_totals($items);

$out = array_map(static function (array $item): array {
    $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price'];

    return [
        'cart_id'    => (int) $item['cart_id'],
        'product_id' => (int) $item['id'],
        'name'       => $item['name'],
        'image'      => $item['image'],
        'quantity'   => (int) $item['quantity'],
        'unit_price' => $unit,
        'line_total' => $unit * (int) $item['quantity'],
        'stock'      => (int) $item['stock'],
    ];
}, $items);

echo json_encode([
    'items'      => $out,
    'subtotal'   => $totals['subtotal'],
    'shipping'   => $totals['shipping'],
    'discount'   => $totals['discount'],
    'total'      => $totals['total'],
    'cart_count' => get_cart_count(),
]);
