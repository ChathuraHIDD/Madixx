<?php

declare(strict_types=1);

/** Escapes a string for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirects to a given path (relative to the site root) and stops execution. */
function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

/** Builds a site-root-relative URL, e.g. base_url('shop.php?category=sunglasses'). */
function base_url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/** Queues a one-time flash message shown on the next page load. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Retrieves and clears all queued flash messages. */
function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flashes;
}

/** Converts a string into a URL-friendly slug. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    return trim($text, '-');
}

/** Formats a numeric price as a currency string, e.g. "$49.00". */
function format_price(float|string|null $amount): string
{
    return '$' . number_format((float) $amount, 2);
}

/** Generates a unique, human-friendly order number from a numeric order id. */
function generate_order_number(int $orderId): string
{
    return 'MX' . str_pad((string) (10000 + $orderId), 5, '0', STR_PAD_LEFT);
}

/** Renders a 5-star rating as inline Font Awesome markup. */
function star_rating_html(float $rating, int $size = 5): string
{
    $html = '<span class="stars" aria-label="' . e((string) $rating) . ' out of ' . $size . ' stars">';
    for ($i = 1; $i <= $size; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star"></i>';
        }
    }
    $html .= '</span>';

    return $html;
}

/** Returns a "time ago" style relative date string. */
function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';

    return date('M j, Y', strtotime($datetime));
}

/** Simple pagination helper: returns [offset, totalPages, currentPage]. */
function paginate(int $totalItems, int $perPage = 12, int $currentPage = 1): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [$offset, $totalPages, $currentPage];
}

/** Renders pagination links preserving existing GET parameters. */
function render_pagination(int $currentPage, int $totalPages): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $params = $_GET;
    $html = '<nav class="pagination" aria-label="Pagination">';

    for ($i = 1; $i <= $totalPages; $i++) {
        $params['page'] = $i;
        $url = '?' . http_build_query($params);
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<a href="' . e($url) . '" class="page-link' . $active . '">' . $i . '</a>';
    }

    $html .= '</nav>';

    return $html;
}

/**
 * Validates and moves an uploaded image into the given upload directory.
 * Returns the stored relative path (e.g. "uploads/products/xxxx.jpg") or null on failure.
 */
function upload_image(array $file, string $subDir): ?string
{
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimes[$mime])) {
        return null;
    }

    $extension = $allowedMimes[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadRoot = dirname(__DIR__) . '/uploads/' . trim($subDir, '/') . '/';

    if (!is_dir($uploadRoot)) {
        mkdir($uploadRoot, 0755, true);
    }

    $destination = $uploadRoot . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return 'uploads/' . trim($subDir, '/') . '/' . $filename;
}

/** Returns the current guest cart identifier (PHP session id). */
function cart_session_id(): string
{
    return session_id();
}

/** Fetches all cart rows (with product data) for the current user or guest session. */
function get_cart_items(): array
{
    $pdo = db();

    if (is_logged_in()) {
        $stmt = $pdo->prepare(
            'SELECT c.id AS cart_id, c.quantity, p.* FROM cart c
             JOIN products p ON p.id = c.product_id
             WHERE c.user_id = :uid ORDER BY c.id DESC'
        );
        $stmt->execute(['uid' => current_user_id()]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT c.id AS cart_id, c.quantity, p.* FROM cart c
             JOIN products p ON p.id = c.product_id
             WHERE c.session_id = :sid AND c.user_id IS NULL ORDER BY c.id DESC'
        );
        $stmt->execute(['sid' => cart_session_id()]);
    }

    return $stmt->fetchAll();
}

/** Returns the total quantity of items in the cart (for the header badge). */
function get_cart_count(): int
{
    $pdo = db();

    if (is_logged_in()) {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :uid');
        $stmt->execute(['uid' => current_user_id()]);
    } else {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = :sid AND user_id IS NULL');
        $stmt->execute(['sid' => cart_session_id()]);
    }

    return (int) $stmt->fetchColumn();
}

/** Computes subtotal/shipping/discount/total for the current cart. */
function get_cart_totals(array $items = null): array
{
    $items ??= get_cart_items();

    $subtotal = 0.0;
    foreach ($items as $item) {
        $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price'];
        $subtotal += $unit * (int) $item['quantity'];
    }

    $shipping = ($subtotal >= 75 || $subtotal <= 0) ? 0.0 : 6.95;
    $discount = 0.0;
    $total = $subtotal + $shipping - $discount;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'discount' => $discount,
        'total'    => $total,
    ];
}

/** Returns the wishlist product ids for the current logged-in user. */
function get_wishlist_product_ids(): array
{
    if (!is_logged_in()) {
        return [];
    }

    $stmt = db()->prepare('SELECT product_id FROM wishlist WHERE user_id = :uid');
    $stmt->execute(['uid' => current_user_id()]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/**
 * Builds a filtered/sorted product listing query from whitelisted GET parameters.
 * Returns [items, totalCount].
 */
function query_products(array $filters, int $page = 1, int $perPage = 12): array
{
    $pdo = db();
    $where = ['p.status = "active"'];
    $params = [];

    if (!empty($filters['category'])) {
        $where[] = 'c.slug = :category';
        $params['category'] = $filters['category'];
    }

    if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
        $placeholders = [];
        foreach ($filters['category_ids'] as $i => $catId) {
            $key = 'cat' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $catId;
        }
        $where[] = 'p.category_id IN (' . implode(',', $placeholders) . ')';
    }

    if (!empty($filters['product_type'])) {
        $where[] = 'p.product_type = :product_type';
        $params['product_type'] = $filters['product_type'];
    }

    if (!empty($filters['skin_type'])) {
        $where[] = 'p.skin_type LIKE :skin_type';
        $params['skin_type'] = '%' . $filters['skin_type'] . '%';
    }

    if (!empty($filters['brand'])) {
        $where[] = 'p.brand = :brand';
        $params['brand'] = $filters['brand'];
    }

    if (isset($filters['min_price']) && $filters['min_price'] !== '') {
        $where[] = 'COALESCE(p.sale_price, p.price) >= :min_price';
        $params['min_price'] = (float) $filters['min_price'];
    }

    if (isset($filters['max_price']) && $filters['max_price'] !== '') {
        $where[] = 'COALESCE(p.sale_price, p.price) <= :max_price';
        $params['max_price'] = (float) $filters['max_price'];
    }

    if (!empty($filters['min_rating'])) {
        $where[] = 'p.rating >= :min_rating';
        $params['min_rating'] = (float) $filters['min_rating'];
    }

    if (!empty($filters['availability']) && $filters['availability'] === 'in_stock') {
        $where[] = 'p.stock > 0';
    }

    if (!empty($filters['featured'])) {
        $where[] = 'p.is_featured = 1';
    }

    if (!empty($filters['bestseller'])) {
        $where[] = 'p.is_bestseller = 1';
    }

    if (!empty($filters['new'])) {
        $where[] = 'p.is_new = 1';
    }

    if (!empty($filters['search'])) {
        $where[] = '(p.name LIKE :search1 OR p.short_description LIKE :search2 OR p.brand LIKE :search3 OR p.product_type LIKE :search4 OR c.name LIKE :search5)';
        $needle = '%' . $filters['search'] . '%';
        $params['search1'] = $needle;
        $params['search2'] = $needle;
        $params['search3'] = $needle;
        $params['search4'] = $needle;
        $params['search5'] = $needle;
    }

    $orderBy = match ($filters['sort'] ?? 'featured') {
        'newest'        => 'p.created_at DESC',
        'bestselling'   => 'p.is_bestseller DESC, p.review_count DESC',
        'price_low'     => 'COALESCE(p.sale_price, p.price) ASC',
        'price_high'    => 'COALESCE(p.sale_price, p.price) DESC',
        'rating'        => 'p.rating DESC',
        default         => 'p.is_featured DESC, p.created_at DESC',
    };

    $whereSql = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    [$offset, $totalPages, $page] = paginate($total, $perPage, $page);

    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON c.id = p.category_id
            WHERE $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [$stmt->fetchAll(), $total, $totalPages, $page];
}

/** Fetches a single active product by slug, or null if not found. */
function get_product_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug AND p.status = "active" LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    $product = $stmt->fetch();

    return $product ?: null;
}

/** Fetches gallery images for a product, main image first. */
function get_product_gallery(int $productId, string $mainImage): array
{
    $stmt = db()->prepare('SELECT image FROM product_images WHERE product_id = :id ORDER BY sort_order ASC');
    $stmt->execute(['id' => $productId]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_unique(array_merge([$mainImage], $images)));
}

/** Fetches approved reviews for a product. */
function get_product_reviews(int $productId): array
{
    $stmt = db()->prepare(
        'SELECT r.*, u.name AS customer_name FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.product_id = :id AND r.status = "approved"
         ORDER BY r.created_at DESC'
    );
    $stmt->execute(['id' => $productId]);

    return $stmt->fetchAll();
}

/** Recalculates and stores a product's aggregate rating/review_count from approved reviews. */
function refresh_product_rating(int $productId): void
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS cnt, COALESCE(AVG(rating), 0) AS avg_rating
         FROM reviews WHERE product_id = :id AND status = "approved"'
    );
    $stmt->execute(['id' => $productId]);
    $row = $stmt->fetch();

    $update = $pdo->prepare('UPDATE products SET rating = :rating, review_count = :count WHERE id = :id');
    $update->execute([
        'rating' => round((float) $row['avg_rating'], 1),
        'count'  => (int) $row['cnt'],
        'id'     => $productId,
    ]);
}

/** Renders a breadcrumb trail. Accepts an array of ['label' => ..., 'url' => ... or null for current page]. */
function render_breadcrumbs(array $trail): string
{
    $html = '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
    $html .= '<li><a href="' . e(base_url('index.php')) . '">Home</a></li>';

    foreach ($trail as $crumb) {
        if (!empty($crumb['url'])) {
            $html .= '<li><a href="' . e($crumb['url']) . '">' . e($crumb['label']) . '</a></li>';
        } else {
            $html .= '<li aria-current="page">' . e($crumb['label']) . '</li>';
        }
    }

    $html .= '</ol></nav>';

    return $html;
}

/** Computes the shipping cost for a given subtotal and delivery method. */
function compute_shipping_cost(float $subtotal, string $deliveryMethod): float
{
    if ($deliveryMethod === 'express') {
        return 14.95;
    }

    return $subtotal >= 75 ? 0.0 : 6.95;
}

/**
 * Places an order inside a transaction: inserts the order + order_items,
 * decrements stock, and returns the new order id/number.
 * Throws on failure (e.g. insufficient stock) so the caller can roll back and report it.
 */
function place_order(array $customer, array $shippingInfo, string $paymentMethod, string $deliveryMethod, array $items, float $subtotal, float $shippingCost, float $discount): array
{
    $pdo = db();
    $total = $subtotal + $shippingCost - $discount;

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO orders (user_id, order_number, customer_name, customer_email, customer_phone, subtotal, shipping, discount, total, payment_method, delivery_method, shipping_address, shipping_city, shipping_province, shipping_postal_code, shipping_country)
             VALUES (:user_id, "", :name, :email, :phone, :subtotal, :shipping, :discount, :total, :payment_method, :delivery_method, :address, :city, :province, :postal_code, :country)'
        );
        $stmt->execute([
            'user_id'       => $customer['user_id'],
            'name'          => $customer['name'],
            'email'         => $customer['email'],
            'phone'         => $customer['phone'],
            'subtotal'      => $subtotal,
            'shipping'      => $shippingCost,
            'discount'      => $discount,
            'total'         => $total,
            'payment_method'  => $paymentMethod,
            'delivery_method' => $deliveryMethod,
            'address'       => $shippingInfo['address'],
            'city'          => $shippingInfo['city'],
            'province'      => $shippingInfo['province'],
            'postal_code'   => $shippingInfo['postal_code'],
            'country'       => $shippingInfo['country'],
        ]);

        $orderId = (int) $pdo->lastInsertId();
        $orderNumber = generate_order_number($orderId);
        $pdo->prepare('UPDATE orders SET order_number = :num WHERE id = :id')->execute(['num' => $orderNumber, 'id' => $orderId]);

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
             VALUES (:order_id, :product_id, :name, :qty, :price, :subtotal)'
        );
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :qty1 WHERE id = :id AND stock >= :qty2');

        foreach ($items as $item) {
            $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price'];
            $qty = (int) $item['quantity'];

            $itemStmt->execute([
                'order_id' => $orderId,
                'product_id' => $item['id'],
                'name'     => $item['name'],
                'qty'      => $qty,
                'price'    => $unit,
                'subtotal' => $unit * $qty,
            ]);

            $stockStmt->execute(['qty1' => $qty, 'id' => $item['id'], 'qty2' => $qty]);
            if ($stockStmt->rowCount() === 0) {
                throw new RuntimeException('Sorry, "' . $item['name'] . '" no longer has enough stock for this order.');
            }
        }

        $pdo->commit();

        return ['order_id' => $orderId, 'order_number' => $orderNumber];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Generates a slug from $baseText that is unique within $table.slug, excluding $excludeId if editing. */
function unique_slug(string $table, string $baseText, ?int $excludeId = null): string
{
    $allowedTables = ['products', 'categories', 'blog_posts'];
    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException('Invalid table for unique_slug().');
    }

    $base = slugify($baseText) ?: 'item';
    $slug = $base;
    $i = 2;

    $sql = "SELECT COUNT(*) FROM $table WHERE slug = :slug" . ($excludeId ? ' AND id != :id' : '');

    while (true) {
        $stmt = db()->prepare($sql);
        $params = ['slug' => $slug];
        if ($excludeId) {
            $params['id'] = $excludeId;
        }
        $stmt->execute($params);

        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }

        $slug = $base . '-' . $i;
        $i++;
    }
}

/** Empties the current cart (user or guest session). */
function clear_cart(): void
{
    $pdo = db();

    if (is_logged_in()) {
        $pdo->prepare('DELETE FROM cart WHERE user_id = :uid')->execute(['uid' => current_user_id()]);
    } else {
        $pdo->prepare('DELETE FROM cart WHERE session_id = :sid AND user_id IS NULL')->execute(['sid' => cart_session_id()]);
    }
}
