<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$cartId = (int) ($_POST['cart_id'] ?? 0);
$pdo = db();

if (is_logged_in()) {
    $stmt = $pdo->prepare('SELECT c.id, c.quantity, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = :id AND c.user_id = :uid');
    $stmt->execute(['id' => $cartId, 'uid' => current_user_id()]);
} else {
    $stmt = $pdo->prepare('SELECT c.id, c.quantity, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = :id AND c.session_id = :sid AND c.user_id IS NULL');
    $stmt->execute(['id' => $cartId, 'sid' => cart_session_id()]);
}
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Item not found in your bag.']);
    exit;
}

if (isset($_POST['delta'])) {
    $newQty = (int) $row['quantity'] + (int) $_POST['delta'];
} else {
    $newQty = (int) ($_POST['quantity'] ?? $row['quantity']);
}

if ($newQty < 1) {
    $pdo->prepare('DELETE FROM cart WHERE id = :id')->execute(['id' => $cartId]);
} else {
    $newQty = min($newQty, (int) $row['stock']);
    $pdo->prepare('UPDATE cart SET quantity = :qty WHERE id = :id')->execute(['qty' => $newQty, 'id' => $cartId]);
}

$totals = get_cart_totals();
echo json_encode(array_merge(['success' => true, 'cart_count' => get_cart_count()], $totals));
