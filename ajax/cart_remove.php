<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$cartId = (int) ($_POST['cart_id'] ?? 0);
$pdo = db();

if (is_logged_in()) {
    $pdo->prepare('DELETE FROM cart WHERE id = :id AND user_id = :uid')->execute(['id' => $cartId, 'uid' => current_user_id()]);
} else {
    $pdo->prepare('DELETE FROM cart WHERE id = :id AND session_id = :sid AND user_id IS NULL')->execute(['id' => $cartId, 'sid' => cart_session_id()]);
}

$totals = get_cart_totals();
echo json_encode(array_merge(['success' => true, 'cart_count' => get_cart_count()], $totals));
