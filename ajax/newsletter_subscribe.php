<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
csrf_require();

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = db()->prepare('INSERT INTO newsletter_subscribers (email) VALUES (:email)');
    $stmt->execute(['email' => $email]);
    echo json_encode(['success' => true, 'message' => 'You are on the list! Welcome to MADIXX.']);
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed — thank you!']);
    } else {
        error_log('Newsletter subscribe failed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
}
