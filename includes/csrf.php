<?php

declare(strict_types=1);

/**
 * Returns the current CSRF token, generating one for this session if needed.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Renders a hidden CSRF input for use inside <form> elements.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifies a submitted CSRF token (POST field or X-CSRF-Token header) against the session token.
 */
function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifies the CSRF token or halts the request with a 403.
 */
function csrf_require(): void
{
    if (!csrf_verify()) {
        http_response_code(403);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh and try again.']);
        } else {
            echo 'Invalid or expired security token. Please go back, refresh the page, and try again.';
        }

        exit;
    }
}
