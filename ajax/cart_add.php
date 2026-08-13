<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

$stmt = db()->prepare('SELECT id, stock FROM products WHERE id = :id AND status = "active"');
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

if ((int) $product['stock'] < 1) {
    echo json_encode(['success' => false, 'message' => 'This product is currently out of stock.']);
    exit;
}

$pdo = db();

if (is_logged_in()) {
    $find = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid');
    $find->execute(['uid' => current_user_id(), 'pid' => $productId]);
} else {
    $find = $pdo->prepare('SELECT id, quantity FROM cart WHERE session_id = :sid AND user_id IS NULL AND product_id = :pid');
    $find->execute(['sid' => cart_session_id(), 'pid' => $productId]);
}
$existing = $find->fetch();

$newQty = min((int) $product['stock'], (int) ($existing['quantity'] ?? 0) + $quantity);

if ($existing) {
    $pdo->prepare('UPDATE cart SET quantity = :qty WHERE id = :id')->execute(['qty' => $newQty, 'id' => $existing['id']]);
} elseif (is_logged_in()) {
    $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)')
        ->execute(['uid' => current_user_id(), 'pid' => $productId, 'qty' => $newQty]);
} else {
    $pdo->prepare('INSERT INTO cart (session_id, product_id, quantity) VALUES (:sid, :pid, :qty)')
        ->execute(['sid' => cart_session_id(), 'pid' => $productId, 'qty' => $newQty]);
}

echo json_encode(['success' => true, 'message' => 'Added to your bag.', 'cart_count' => get_cart_count()]);
