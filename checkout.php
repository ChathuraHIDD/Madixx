<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$buyNowProductId = (int) ($_GET['buy_now'] ?? $_POST['buy_now'] ?? 0);
$user = current_user();

/** Resolves the item list this checkout session is for: a single "buy now" product, or the full cart. */
function checkout_items(int $buyNowProductId): array
{
    if ($buyNowProductId > 0) {
        $stmt = db()->prepare('SELECT * FROM products WHERE id = :id AND status = "active"');
        $stmt->execute(['id' => $buyNowProductId]);
        $product = $stmt->fetch();

        if (!$product || (int) $product['stock'] < 1) {
            return [];
        }

        $product['quantity'] = 1;

        return [$product];
    }

    return get_cart_items();
}

$items = checkout_items($buyNowProductId);

if (!$items) {
    flash('error', $buyNowProductId ? 'That product is no longer available.' : 'Your bag is empty. Add something you love before checking out.');
    redirect('cart.php');
}

$subtotal = 0.0;
foreach ($items as $item) {
    $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price'];
    $subtotal += $unit * (int) $item['quantity'];
}
$discount = 0.0;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $province = trim((string) ($_POST['province'] ?? ''));
    $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
    $country = trim((string) ($_POST['country'] ?? 'Sri Lanka'));
    $deliveryMethod = in_array($_POST['delivery_method'] ?? '', ['standard', 'express'], true) ? $_POST['delivery_method'] : 'standard';
    $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'bank_transfer'], true) ? $_POST['payment_method'] : 'cod';

    if ($name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($address === '') $errors[] = 'Shipping address is required.';
    if ($city === '') $errors[] = 'City is required.';

    if (!$errors) {
        $shippingCost = compute_shipping_cost($subtotal, $deliveryMethod);

        try {
            $result = place_order(
                ['user_id' => current_user_id(), 'name' => $name, 'email' => $email, 'phone' => $phone],
                ['address' => $address, 'city' => $city, 'province' => $province, 'postal_code' => $postalCode, 'country' => $country],
                $paymentMethod,
                $deliveryMethod,
                $items,
                $subtotal,
                $shippingCost,
                $discount
            );

            if (!$buyNowProductId) {
                clear_cart();
            }

            flash('success', 'Your order has been placed successfully!');
            redirect('order-confirmation.php?order=' . urlencode($result['order_number']));
        } catch (Throwable $e) {
            error_log('Order placement failed: ' . $e->getMessage());
            $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'We could not process your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout — Glowelle';
$metaDescription = 'Complete your Glowelle order.';
$canonicalPath = 'checkout.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <h1 style="font-size:2.2rem;margin-bottom:8px;">Checkout</h1>
  <p style="color:var(--text-muted);margin-bottom:40px;">Secure checkout — Cash on Delivery or Bank Transfer.</p>

  <?php if ($errors): ?>
  <div class="flash flash-error" style="margin:0 0 24px;max-width:none;">
    <?= implode('<br>', array_map('e', $errors)) ?>
  </div>
  <?php endif; ?>

  <div class="checkout-steps">
    <div class="checkout-step active" data-step="1"><span class="num">1</span>Info</div>
    <div class="checkout-step" data-step="2"><span class="num">2</span>Shipping</div>
    <div class="checkout-step" data-step="3"><span class="num">3</span>Delivery</div>
    <div class="checkout-step" data-step="4"><span class="num">4</span>Payment</div>
    <div class="checkout-step" data-step="5"><span class="num">5</span>Review</div>
  </div>

  <div class="checkout-layout">
    <form method="post" id="checkoutForm">
      <?= csrf_field() ?>
      <?php if ($buyNowProductId): ?><input type="hidden" name="buy_now" value="<?= (int) $buyNowProductId ?>"><?php endif; ?>

      <div class="checkout-panel active" data-step="1">
        <h3 style="margin-bottom:24px;">Customer Information</h3>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" id="cf_name" required value="<?= e((string) ($_POST['name'] ?? $user['name'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" id="cf_email" required value="<?= e((string) ($_POST['email'] ?? $user['email'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" id="cf_phone" required value="<?= e((string) ($_POST['phone'] ?? $user['phone'] ?? '')) ?>">
          </div>
        </div>
        <button type="button" class="btn btn-primary checkout-next">Continue to Shipping</button>
      </div>

      <div class="checkout-panel" data-step="2">
        <h3 style="margin-bottom:24px;">Shipping Address</h3>
        <div class="form-group">
          <label>Street Address</label>
          <input type="text" name="address" id="cf_address" required value="<?= e((string) ($_POST['address'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>City</label>
            <input type="text" name="city" id="cf_city" required value="<?= e((string) ($_POST['city'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label>Province</label>
            <input type="text" name="province" id="cf_province" value="<?= e((string) ($_POST['province'] ?? '')) ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Postal Code</label>
            <input type="text" name="postal_code" id="cf_postal" value="<?= e((string) ($_POST['postal_code'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label>Country</label>
            <input type="text" name="country" id="cf_country" value="<?= e((string) ($_POST['country'] ?? 'Sri Lanka')) ?>">
          </div>
        </div>
        <div class="checkout-nav-btns">
          <button type="button" class="btn btn-outline checkout-prev">Back</button>
          <button type="button" class="btn btn-primary checkout-next">Continue to Delivery</button>
        </div>
      </div>

      <div class="checkout-panel" data-step="3">
        <h3 style="margin-bottom:24px;">Delivery Method</h3>
        <fieldset>
          <label class="delivery-option selected">
            <input type="radio" name="delivery_method" value="standard" checked>
            <div><strong>Standard Delivery</strong><small>3–5 business days · <span id="standardPriceLabel"><?= $subtotal >= 75 ? 'Free' : format_price(6.95) ?></span></small></div>
          </label>
          <label class="delivery-option">
            <input type="radio" name="delivery_method" value="express">
            <div><strong>Express Delivery</strong><small>1–2 business days · <?= format_price(14.95) ?></small></div>
          </label>
        </fieldset>
        <div class="checkout-nav-btns">
          <button type="button" class="btn btn-outline checkout-prev">Back</button>
          <button type="button" class="btn btn-primary checkout-next">Continue to Payment</button>
        </div>
      </div>

      <div class="checkout-panel" data-step="4">
        <h3 style="margin-bottom:24px;">Payment Method</h3>
        <fieldset>
          <label class="payment-option selected">
            <input type="radio" name="payment_method" value="cod" checked>
            <div><strong>Cash on Delivery</strong><small>Pay in cash when your order arrives.</small></div>
          </label>
          <label class="payment-option">
            <input type="radio" name="payment_method" value="bank_transfer">
            <div><strong>Bank Transfer</strong><small>Our bank details will be shown on your order confirmation.</small></div>
          </label>
        </fieldset>
        <div class="checkout-nav-btns">
          <button type="button" class="btn btn-outline checkout-prev">Back</button>
          <button type="button" class="btn btn-primary checkout-next">Review Order</button>
        </div>
      </div>

      <div class="checkout-panel" data-step="5">
        <h3 style="margin-bottom:24px;">Review Your Order</h3>

        <div class="review-block">
          <h4>Items</h4>
          <?php foreach ($items as $item): ?>
          <?php $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price']; ?>
          <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:0.92rem;">
            <span><?= e($item['name']) ?> &times; <?= (int) $item['quantity'] ?></span>
            <span><?= format_price($unit * (int) $item['quantity']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="review-block">
          <h4>Delivery Address</h4>
          <p id="reviewAddress" style="color:var(--text-muted);">—</p>
        </div>

        <div class="review-block">
          <h4>Delivery &amp; Payment</h4>
          <p id="reviewMethods" style="color:var(--text-muted);">—</p>
        </div>

        <div class="checkout-nav-btns">
          <button type="button" class="btn btn-outline checkout-prev">Back</button>
          <button type="submit" class="btn btn-primary">Place Order</button>
        </div>
      </div>
    </form>

    <div class="summary-card">
      <h3 style="margin-bottom:20px;">Order Summary</h3>
      <?php foreach ($items as $item): ?>
      <?php $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price']; ?>
      <div class="mini-cart-item" style="border-bottom:1px solid var(--border-soft);">
        <img src="<?= e(base_url($item['image'])) ?>" alt="<?= e($item['name']) ?>">
        <div class="mini-cart-item-info">
          <h4><?= e($item['name']) ?></h4>
          <span>Qty <?= (int) $item['quantity'] ?> &times; <?= format_price($unit) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="summary-row" style="margin-top:16px;"><span>Subtotal</span><strong id="summarySubtotal"><?= format_price($subtotal) ?></strong></div>
      <div class="summary-row"><span>Shipping</span><strong id="summaryShipping"><?= $subtotal >= 75 ? 'Free' : format_price(6.95) ?></strong></div>
      <div class="summary-row"><span>Discount</span><strong id="summaryDiscount">-<?= format_price($discount) ?></strong></div>
      <div class="summary-row total"><span>Total</span><strong id="summaryTotal"><?= format_price($subtotal - $discount + ($subtotal >= 75 ? 0 : 6.95)) ?></strong></div>
    </div>
  </div>
</div>

<script>
(function () {
  const SUBTOTAL = <?= json_encode($subtotal) ?>;
  const DISCOUNT = <?= json_encode($discount) ?>;
  function money(n) { return '$' + Number(n).toFixed(2); }

  window.onDeliveryMethodChange = function (method) {
    const shipping = method === 'express' ? 14.95 : (SUBTOTAL >= 75 ? 0 : 6.95);
    document.getElementById('summaryShipping').textContent = shipping > 0 ? money(shipping) : 'Free';
    document.getElementById('summaryTotal').textContent = money(SUBTOTAL - DISCOUNT + shipping);
  };

  document.querySelector('.checkout-panel[data-step="4"] .checkout-next')?.addEventListener('click', function () {
    const address = [
      document.getElementById('cf_address').value,
      document.getElementById('cf_city').value,
      document.getElementById('cf_province').value,
      document.getElementById('cf_postal').value,
      document.getElementById('cf_country').value,
    ].filter(Boolean).join(', ');
    document.getElementById('reviewAddress').textContent = address || '—';

    const delivery = document.querySelector('input[name="delivery_method"]:checked');
    const payment = document.querySelector('input[name="payment_method"]:checked');
    const deliveryLabel = delivery && delivery.value === 'express' ? 'Express Delivery' : 'Standard Delivery';
    const paymentLabel = payment && payment.value === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery';
    document.getElementById('reviewMethods').textContent = deliveryLabel + ' · ' + paymentLabel;
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
