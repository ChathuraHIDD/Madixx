<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to save items to your wishlist.']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid');
$stmt->execute(['uid' => current_user_id(), 'pid' => $productId]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare('DELETE FROM wishlist WHERE id = :id')->execute(['id' => $existing['id']]);
    echo json_encode(['success' => true, 'active' => false, 'message' => 'Removed from your wishlist.']);
} else {
    $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (:uid, :pid)')
        ->execute(['uid' => current_user_id(), 'pid' => $productId]);
    echo json_encode(['success' => true, 'active' => true, 'message' => 'Added to your wishlist.']);
}
