<?php

declare(strict_types=1);

/** Whether a customer/admin is currently logged in. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Whether the logged-in user has the admin role. */
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** Fetches the full current user row, or null if not logged in / no longer exists. */
function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $stmt = db()->prepare('SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = :id');
    $stmt->execute(['id' => current_user_id()]);
    $user = $stmt->fetch();

    return $cached = ($user ?: null);
}

/** Logs a user in: regenerates the session, stores identity, merges any guest cart. */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    merge_guest_cart_into_user((int) $user['id']);
}

/** Logs the current user out and destroys the session. */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Redirects to login if the visitor isn't authenticated. */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? base_url('account.php');
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

/** Halts with a 403 unless the current user is an admin (customer-facing guard). */
function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

/**
 * Moves any guest-session cart rows into the logged-in user's cart,
 * merging quantities when the same product already exists in the user's cart.
 */
function merge_guest_cart_into_user(int $userId): void
{
    $pdo = db();
    $sessionId = cart_session_id();

    $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart WHERE session_id = :sid AND user_id IS NULL');
    $stmt->execute(['sid' => $sessionId]);
    $guestItems = $stmt->fetchAll();

    if (!$guestItems) {
        return;
    }

    $find = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid');
    $updateQty = $pdo->prepare('UPDATE cart SET quantity = :qty WHERE id = :id');
    $insert = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)');

    foreach ($guestItems as $item) {
        $find->execute(['uid' => $userId, 'pid' => $item['product_id']]);
        $existing = $find->fetch();

        if ($existing) {
            $updateQty->execute(['qty' => $existing['quantity'] + $item['quantity'], 'id' => $existing['id']]);
        } else {
            $insert->execute(['uid' => $userId, 'pid' => $item['product_id'], 'qty' => $item['quantity']]);
        }
    }

    $delete = $pdo->prepare('DELETE FROM cart WHERE session_id = :sid AND user_id IS NULL');
    $delete->execute(['sid' => $sessionId]);
}
