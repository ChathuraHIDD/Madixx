<?php

declare(strict_types=1);

/**
 * Stripe webhook endpoint. Configure this URL in the Stripe Dashboard
 * (Developers → Webhooks) for the "checkout.session.completed" and
 * "checkout.session.async_payment_succeeded" events:
 *   https://yourdomain.com/Madixx/stripe-webhook.php
 *
 * This is the reliable source of truth for payment confirmation — it
 * still fires even if the customer closes their browser tab before
 * being redirected back to order-confirmation.php.
 */

require_once __DIR__ . '/includes/init.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

header('Content-Type: application/json');

if ($payload === '' || $sigHeader === '' || !stripe_verify_webhook_signature($payload, $sigHeader, STRIPE_WEBHOOK_SECRET)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature.']);
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event) || !isset($event['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Malformed payload.']);
    exit;
}

if (in_array($event['type'], ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
    $session = $event['data']['object'] ?? [];
    $orderId = (int) ($session['metadata']['order_id'] ?? 0);
    $paymentIntent = (string) ($session['payment_intent'] ?? '');

    if ($orderId > 0 && ($session['payment_status'] ?? '') === 'paid') {
        stripe_mark_order_paid($orderId, $paymentIntent);
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
