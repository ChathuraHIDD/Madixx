<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$orderNumber = trim((string) ($_GET['order'] ?? ''));

$stmt = db()->prepare('SELECT * FROM orders WHERE order_number = :num');
$stmt->execute(['num' => $orderNumber]);
$order = $stmt->fetch();

$forbidden = $order && $order['user_id'] !== null
    && (!is_logged_in() || ((int) $order['user_id'] !== current_user_id() && !is_admin()));

if (!$order || $forbidden || $order['payment_method'] !== 'card') {
    http_response_code(404);
    $pageTitle = 'Order Not Found — MADIXX';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><i class="fa-regular fa-face-frown"></i><h2>Order not found</h2><p>We could not find that order.</p><a href="' . e(base_url('index.php')) . '" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Already paid (e.g. the customer hit back after paying) — just show the confirmation.
if ($order['payment_status'] === 'paid') {
    redirect('order-confirmation.php?order=' . urlencode($order['order_number']));
}

$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemsStmt->execute(['id' => $order['id']]);
$orderItems = $itemsStmt->fetchAll();

try {
    $session = stripe_create_checkout_session($order, $orderItems);

    db()->prepare('UPDATE orders SET stripe_session_id = :sid WHERE id = :id')
        ->execute(['sid' => $session['id'], 'id' => $order['id']]);

    header('Location: ' . $session['url']);
    exit;
} catch (StripeApiException $e) {
    error_log('Stripe checkout session failed for order ' . $orderNumber . ': ' . $e->getMessage());
    flash('error', 'We could not start the Stripe payment (' . $e->getMessage() . '). Your order has been saved — please contact us or try again from your order confirmation page.');
    redirect('order-confirmation.php?order=' . urlencode($order['order_number']));
}
