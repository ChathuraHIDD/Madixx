<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$items = get_cart_items();
$totals = get_cart_totals($items);

$pageTitle = 'Your Bag — MADIXX';
$metaDescription = 'Review the items in your MADIXX shopping bag.';
$canonicalPath = 'cart.php';

require __DIR__ . '/includes/header.php';
?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'Your Bag', 'url' => null]]) ?>
  <h1 style="font-size:2.2rem;margin-bottom:32px;">Your Bag</h1>

  <?php if (!$items): ?>
  <div class="empty-state">
    <i class="fa-solid fa-bag-shopping"></i>
    <h2>Your bag is empty</h2>
    <p>Looks like you haven't added anything yet. Let's find something you'll love.</p>
    <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-primary">Start Shopping</a>
  </div>
  <?php else: ?>
  <div class="cart-layout">
    <table class="cart-table">
      <thead>
        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
      </thead>
      <tbody id="cartTableBody">
        <?php foreach ($items as $item): ?>
        <?php
          $unit = $item['sale_price'] !== null ? (float) $item['sale_price'] : (float) $item['price'];
        ?>
        <tr data-cart-id="<?= (int) $item['cart_id'] ?>" data-unit-price="<?= e((string) $unit) ?>">
          <td>
            <div class="cart-product">
              <img src="<?= e(base_url($item['image'])) ?>" alt="<?= e($item['name']) ?>">
              <div>
                <h4 style="margin-bottom:4px;"><a href="<?= e(base_url('product.php?slug=' . $item['slug'])) ?>"><?= e($item['name']) ?></a></h4>
                <span style="font-size:0.82rem;color:var(--text-muted);">SKU: <?= e($item['sku']) ?></span>
              </div>
            </div>
          </td>
          <td><?= format_price($unit) ?></td>
          <td>
            <div class="qty-selector">
              <button type="button" class="qty-minus cart-qty-btn" aria-label="Decrease quantity">−</button>
              <input type="number" class="js-cart-qty" value="<?= (int) $item['quantity'] ?>" min="1" data-max="<?= (int) $item['stock'] ?>" aria-label="Quantity">
              <button type="button" class="qty-plus cart-qty-btn" aria-label="Increase quantity">+</button>
            </div>
          </td>
          <td class="line-total"><?= format_price($unit * (int) $item['quantity']) ?></td>
          <td><button type="button" class="cart-remove" aria-label="Remove item"><i class="fa-solid fa-trash-can"></i></button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="summary-card">
      <h3 style="margin-bottom:20px;">Order Summary</h3>
      <div class="summary-row"><span>Subtotal</span><strong id="summarySubtotal"><?= format_price($totals['subtotal']) ?></strong></div>
      <div class="summary-row"><span>Shipping</span><strong id="summaryShipping"><?= $totals['shipping'] > 0 ? format_price($totals['shipping']) : 'Free' ?></strong></div>
      <div class="summary-row"><span>Discount</span><strong id="summaryDiscount">-<?= format_price($totals['discount']) ?></strong></div>
      <div class="summary-row total"><span>Total</span><strong id="summaryTotal"><?= format_price($totals['total']) ?></strong></div>
      <p style="font-size:0.8rem;color:var(--text-muted);margin:12px 0 20px;">Free shipping on orders over $75.</p>
      <a href="<?= e(base_url('checkout.php')) ?>" class="btn btn-primary btn-block">Proceed to Checkout</a>
      <a href="<?= e(base_url('shop.php')) ?>" class="btn btn-text" style="display:block;text-align:center;margin-top:14px;">Continue Shopping</a>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
(function () {
  const CFG = window.MADIXX;
  function money(n) { return '$' + Number(n).toFixed(2); }

  function updateSummary(data) {
    document.getElementById('summarySubtotal').textContent = money(data.subtotal);
    document.getElementById('summaryShipping').textContent = data.shipping > 0 ? money(data.shipping) : 'Free';
    document.getElementById('summaryDiscount').textContent = '-' + money(data.discount);
    document.getElementById('summaryTotal').textContent = money(data.total);
    document.querySelectorAll('#cartCount, .cart-count').forEach((el) => (el.textContent = data.cart_count));
  }

  async function updateCartRow(row, quantity) {
    const cartId = row.dataset.cartId;
    const res = await fetch(CFG.baseUrl + 'ajax/cart_update.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ csrf_token: CFG.csrfToken, cart_id: cartId, quantity }),
    });
    const data = await res.json();
    if (data.success) {
      const unit = parseFloat(row.dataset.unitPrice);
      row.querySelector('.line-total').textContent = money(unit * quantity);
      updateSummary(data);
    }
  }

  document.querySelectorAll('.cart-qty-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr');
      const input = row.querySelector('.js-cart-qty');
      const max = parseInt(input.dataset.max, 10) || 99;
      let qty = parseInt(input.value, 10) || 1;
      qty = btn.classList.contains('qty-plus') ? Math.min(max, qty + 1) : Math.max(1, qty - 1);
      input.value = qty;
      updateCartRow(row, qty);
    });
  });

  document.querySelectorAll('.js-cart-qty').forEach((input) => {
    input.addEventListener('change', () => {
      const row = input.closest('tr');
      const max = parseInt(input.dataset.max, 10) || 99;
      const qty = Math.max(1, Math.min(max, parseInt(input.value, 10) || 1));
      input.value = qty;
      updateCartRow(row, qty);
    });
  });

  document.querySelectorAll('.cart-remove').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const row = btn.closest('tr');
      const res = await fetch(CFG.baseUrl + 'ajax/cart_remove.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ csrf_token: CFG.csrfToken, cart_id: row.dataset.cartId }),
      });
      const data = await res.json();
      if (data.success) {
        row.remove();
        updateSummary(data);
        if (!document.querySelectorAll('#cartTableBody tr').length) {
          window.location.reload();
        }
      }
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
