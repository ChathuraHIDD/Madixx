<?php

declare(strict_types=1);

/**
 * Minimal Stripe API client (Checkout Sessions) built on cURL — no SDK/composer
 * dependency, consistent with the rest of this codebase.
 */

class StripeApiException extends RuntimeException
{
}

/**
 * Sends a signed request to the Stripe API and returns the decoded JSON body.
 * Stripe's API accepts application/x-www-form-urlencoded bodies, including
 * PHP's native bracket notation for nested/array params (e.g. line_items[0][price]).
 */
function stripe_request(string $method, string $endpoint, array $params = []): array
{
    if (STRIPE_SECRET_KEY === '' || str_starts_with(STRIPE_SECRET_KEY, 'sk_test_REPLACE')) {
        throw new StripeApiException('Stripe is not configured yet. Add your API keys to config/stripe.php.');
    }

    $url = 'https://api.stripe.com/v1/' . ltrim($endpoint, '/');
    $isGet = strtoupper($method) === 'GET';
    if ($isGet && $params) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER     => ['Stripe-Version: 2024-06-20'],
        CURLOPT_TIMEOUT        => 30,
    ];

    if (!$isGet) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($params);
    }

    curl_setopt_array($ch, $options);
    $body = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        throw new StripeApiException('Could not reach Stripe: ' . $error);
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new StripeApiException('Stripe returned an unexpected response.');
    }

    if ($status >= 400) {
        $message = $data['error']['message'] ?? 'Stripe request failed.';
        throw new StripeApiException($message);
    }

    return $data;
}

/**
 * Creates a Stripe Checkout Session for an order and returns the session
 * array (session['url'] is where the customer should be redirected to pay).
 */
function stripe_create_checkout_session(array $order, array $orderItems): array
{
    $params = [
        'mode'                    => 'payment',
        'success_url'             => base_url('order-confirmation.php?order=' . rawurlencode($order['order_number']) . '&session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url'              => base_url('order-confirmation.php?order=' . rawurlencode($order['order_number']) . '&stripe_cancelled=1'),
        'customer_email'          => $order['customer_email'],
        'client_reference_id'     => $order['order_number'],
        'metadata'                => ['order_id' => (string) $order['id'], 'order_number' => $order['order_number']],
        'payment_intent_data'     => ['metadata' => ['order_id' => (string) $order['id'], 'order_number' => $order['order_number']]],
    ];

    $i = 0;
    foreach ($orderItems as $item) {
        $params['line_items'][$i]['quantity'] = (int) $item['quantity'];
        $params['line_items'][$i]['price_data']['currency'] = STRIPE_CURRENCY;
        $params['line_items'][$i]['price_data']['unit_amount'] = (int) round((float) $item['price'] * 100);
        $params['line_items'][$i]['price_data']['product_data']['name'] = $item['product_name'];
        $i++;
    }

    if ((float) $order['shipping'] > 0) {
        $params['line_items'][$i]['quantity'] = 1;
        $params['line_items'][$i]['price_data']['currency'] = STRIPE_CURRENCY;
        $params['line_items'][$i]['price_data']['unit_amount'] = (int) round((float) $order['shipping'] * 100);
        $params['line_items'][$i]['price_data']['product_data']['name'] = 'Shipping (' . ($order['delivery_method'] === 'express' ? 'Express' : 'Standard') . ')';
    }

    return stripe_request('POST', 'checkout/sessions', $params);
}

/** Retrieves a Checkout Session by id (used to confirm payment on the success return). */
function stripe_retrieve_checkout_session(string $sessionId): array
{
    return stripe_request('GET', 'checkout/sessions/' . rawurlencode($sessionId));
}

/**
 * Verifies a Stripe webhook signature per Stripe's documented scheme
 * (https://docs.stripe.com/webhooks#verify-manually) without the SDK.
 */
function stripe_verify_webhook_signature(string $payload, string $sigHeader, string $secret): bool
{
    $parts = [];
    foreach (explode(',', $sigHeader) as $pair) {
        [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
        $parts[$key][] = $value;
    }

    $timestamp = $parts['t'][0] ?? '';
    $signatures = $parts['v1'] ?? [];

    if ($timestamp === '' || !$signatures) {
        return false;
    }

    // Reject payloads older than 5 minutes to guard against replay attacks.
    if (abs(time() - (int) $timestamp) > 300) {
        return false;
    }

    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }

    return false;
}

/**
 * Marks an order as paid via Stripe (idempotent — a no-op if it's already
 * marked paid, so the success-page check and the webhook can't double-fire).
 */
function stripe_mark_order_paid(int $orderId, string $paymentIntentId): bool
{
    $stmt = db()->prepare(
        "UPDATE orders
         SET payment_status = 'paid', stripe_payment_intent = :pi,
             order_status = IF(order_status = 'pending', 'confirmed', order_status)
         WHERE id = :id AND payment_status = 'unpaid'"
    );
    $stmt->execute(['pi' => $paymentIntentId, 'id' => $orderId]);

    return $stmt->rowCount() > 0;
}
