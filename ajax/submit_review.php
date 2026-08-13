<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to write a review.']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$review = trim((string) ($_POST['review'] ?? ''));

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please choose a rating between 1 and 5.']);
    exit;
}

if (mb_strlen($review) < 10) {
    echo json_encode(['success' => false, 'message' => 'Please write a review of at least 10 characters.']);
    exit;
}

$pdo = db();
$productStmt = $pdo->prepare('SELECT id FROM products WHERE id = :id');
$productStmt->execute(['id' => $productId]);
if (!$productStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO reviews (user_id, product_id, rating, review, status) VALUES (:uid, :pid, :rating, :review, "pending")');
    $stmt->execute([
        'uid'    => current_user_id(),
        'pid'    => $productId,
        'rating' => $rating,
        'review' => $review,
    ]);
    echo json_encode(['success' => true, 'message' => 'Thank you! Your review has been submitted and is awaiting approval.']);
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
    } else {
        error_log('Review submit failed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
}
