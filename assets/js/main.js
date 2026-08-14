/* =====================================================================
   MADIXX — Core front-end behaviour (vanilla JS, no framework)
   ===================================================================== */
(function () {
  'use strict';

  const CFG = window.MADIXX || { baseUrl: '/', csrfToken: '', isLoggedIn: false };

  function url(path) {
    return CFG.baseUrl.replace(/\/$/, '/') + path.replace(/^\//, '');
  }

  async function postJSON(path, data) {
    const body = new URLSearchParams({ csrf_token: CFG.csrfToken, ...data });
    const res = await fetch(url(path), {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body,
    });
    return res.json();
  }

  async function getJSON(path) {
    const res = await fetch(url(path), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    return res.json();
  }

  /* -------------------------------------------------------------------
     Toasts
     ------------------------------------------------------------------- */
  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="fa-solid fa-' + (type === 'success' ? 'circle-check' : 'circle-exclamation') + '"></i><span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(8px)';
      setTimeout(() => toast.remove(), 250);
    }, 2800);
  }
  window.showToast = showToast;

  /* -------------------------------------------------------------------
     Mobile menu + search + nav overlay
     ------------------------------------------------------------------- */
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileNav = document.getElementById('mobileNav');
  const navOverlay = document.getElementById('navOverlay');
  const searchToggle = document.getElementById('searchToggle');
  const searchBar = document.getElementById('searchBar');

  function closeMobileNav() {
    mobileNav?.classList.remove('open');
    navOverlay?.classList.remove('open');
    hamburgerBtn?.setAttribute('aria-expanded', 'false');
  }

  hamburgerBtn?.addEventListener('click', () => {
    const isOpen = mobileNav.classList.toggle('open');
    navOverlay.classList.toggle('open', isOpen);
    hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
  });
  navOverlay?.addEventListener('click', closeMobileNav);

  searchToggle?.addEventListener('click', () => {
    const isOpen = searchBar.classList.toggle('open');
    searchToggle.setAttribute('aria-expanded', String(isOpen));
    if (isOpen) searchBar.querySelector('input')?.focus();
  });

  /* -------------------------------------------------------------------
     Cart drawer
     ------------------------------------------------------------------- */
  const cartDrawer = document.getElementById('cartDrawer');
  const cartToggle = document.getElementById('cartToggle');
  const cartDrawerClose = document.getElementById('cartDrawerClose');
  const cartDrawerBody = document.getElementById('cartDrawerBody');
  const cartDrawerSubtotal = document.getElementById('cartDrawerSubtotal');
  const cartCountEls = () => document.querySelectorAll('#cartCount, .cart-count');

  function moneyFmt(n) {
    return '$' + Number(n).toFixed(2);
  }

  function renderCartDrawer(data) {
    if (!cartDrawerBody) return;
    if (!data.items || data.items.length === 0) {
      cartDrawerBody.innerHTML = '<div class="cart-drawer-empty"><i class="fa-solid fa-bag-shopping" style="font-size:1.8rem;color:var(--color-dusty-rose);margin-bottom:12px;display:block;"></i><p>Your bag is empty.</p></div>';
    } else {
      cartDrawerBody.innerHTML = data.items.map((item) => `
        <div class="mini-cart-item" data-cart-id="${item.cart_id}">
          <img src="${url(item.image)}" alt="${escapeHtml(item.name)}">
          <div class="mini-cart-item-info">
            <h4>${escapeHtml(item.name)}</h4>
            <span>${moneyFmt(item.unit_price)}</span>
            <div class="mini-cart-item-qty">
              <button type="button" class="qty-dec" data-cart-id="${item.cart_id}" aria-label="Decrease quantity">-</button>
              <span>${item.quantity}</span>
              <button type="button" class="qty-inc" data-cart-id="${item.cart_id}" aria-label="Increase quantity">+</button>
            </div>
            <button type="button" class="mini-cart-remove" data-cart-id="${item.cart_id}">Remove</button>
          </div>
        </div>
      `).join('');
    }
    if (cartDrawerSubtotal) cartDrawerSubtotal.textContent = moneyFmt(data.subtotal || 0);
    cartCountEls().forEach((el) => (el.textContent = data.cart_count ?? 0));
  }

  async function refreshCartDrawer() {
    if (!cartDrawerBody) return;
    cartDrawerBody.innerHTML = '<p class="cart-drawer-loading">Loading…</p>';
    try {
      const data = await getJSON('ajax/cart_contents.php');
      renderCartDrawer(data);
    } catch (e) {
      cartDrawerBody.innerHTML = '<p class="cart-drawer-empty">Could not load your bag.</p>';
    }
  }

  function openCartDrawer() {
    cartDrawer?.classList.add('open');
    cartDrawer?.setAttribute('aria-hidden', 'false');
    cartToggle?.setAttribute('aria-expanded', 'true');
    refreshCartDrawer();
  }
  function closeCartDrawer() {
    cartDrawer?.classList.remove('open');
    cartDrawer?.setAttribute('aria-hidden', 'true');
    cartToggle?.setAttribute('aria-expanded', 'false');
  }

  cartToggle?.addEventListener('click', openCartDrawer);
  cartDrawerClose?.addEventListener('click', closeCartDrawer);

  cartDrawerBody?.addEventListener('click', async (e) => {
    const target = e.target.closest('button');
    if (!target) return;
    const cartId = target.dataset.cartId;
    if (!cartId) return;

    if (target.classList.contains('mini-cart-remove')) {
      const data = await postJSON('ajax/cart_remove.php', { cart_id: cartId });
      if (data.success) refreshCartDrawer();
    } else if (target.classList.contains('qty-inc') || target.classList.contains('qty-dec')) {
      const delta = target.classList.contains('qty-inc') ? 1 : -1;
      const data = await postJSON('ajax/cart_update.php', { cart_id: cartId, delta });
      if (data.success) refreshCartDrawer();
      if (typeof window.onCartPageUpdate === 'function') window.onCartPageUpdate();
    }
  });

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  /* -------------------------------------------------------------------
     Add to cart (product cards, PDP, quick view)
     ------------------------------------------------------------------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn || btn.disabled) return;
    e.preventDefault();

    const productId = btn.dataset.productId;
    const qtyInput = btn.closest('form, .pdp-actions, .modal-box')?.querySelector('.js-qty-input');
    const quantity = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;

    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Adding…';

    try {
      const data = await postJSON('ajax/cart_add.php', { product_id: productId, quantity });
      if (data.success) {
        showToast(data.message || 'Added to your bag.');
        cartCountEls().forEach((el) => (el.textContent = data.cart_count ?? 0));
        if (typeof window.onCartPageUpdate === 'function') window.onCartPageUpdate();
      } else {
        showToast(data.message || 'Could not add item.', 'error');
      }
    } catch (err) {
      showToast('Something went wrong. Please try again.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });

  /* -------------------------------------------------------------------
     Wishlist toggle
     ------------------------------------------------------------------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.wishlist-toggle');
    if (!btn) return;
    e.preventDefault();

    if (!CFG.isLoggedIn) {
      showToast('Please log in to save items to your wishlist.', 'error');
      setTimeout(() => (window.location.href = url('login.php')), 900);
      return;
    }

    const productId = btn.dataset.productId;
    const data = await postJSON('ajax/wishlist_toggle.php', { product_id: productId });
    if (data.success) {
      btn.classList.toggle('active', data.active);
      btn.setAttribute('aria-pressed', String(data.active));
      const icon = btn.querySelector('i');
      if (icon) icon.className = data.active ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
      showToast(data.message);
      if (typeof window.onWishlistPageUpdate === 'function' && !data.active) {
        window.onWishlistPageUpdate(productId);
      }
    } else {
      showToast(data.message || 'Could not update wishlist.', 'error');
    }
  });

  /* -------------------------------------------------------------------
     Quantity selector (PDP)
     ------------------------------------------------------------------- */
  document.querySelectorAll('.qty-selector').forEach((selector) => {
    const input = selector.querySelector('.js-qty-input');
    const max = parseInt(input?.dataset.max || '99', 10);
    selector.querySelector('.qty-plus')?.addEventListener('click', () => {
      input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
    });
    selector.querySelector('.qty-minus')?.addEventListener('click', () => {
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
    });
  });

  /* -------------------------------------------------------------------
     Quick view modal
     ------------------------------------------------------------------- */
  const quickViewOverlay = document.getElementById('quickViewOverlay');
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.quick-view-btn');
    if (!btn || !quickViewOverlay) return;
    e.preventDefault();

    const slug = btn.dataset.productSlug;
    quickViewOverlay.classList.add('open');
    quickViewOverlay.querySelector('.modal-box').innerHTML = '<button type="button" class="modal-close" id="quickViewClose">&times;</button><p style="padding:60px 0;text-align:center;color:var(--text-muted);">Loading…</p>';

    try {
      const data = await getJSON('ajax/quick_view.php?slug=' + encodeURIComponent(slug));
      if (data.success) {
        quickViewOverlay.querySelector('.modal-box').innerHTML = data.html;
      }
    } catch (err) {
      showToast('Could not load product.', 'error');
    }
  });

  document.addEventListener('click', (e) => {
    if (e.target === quickViewOverlay || e.target.id === 'quickViewClose' || e.target.closest('#quickViewClose')) {
      quickViewOverlay?.classList.remove('open');
    }
  });

  /* -------------------------------------------------------------------
     Newsletter form
     ------------------------------------------------------------------- */
  const newsletterForm = document.getElementById('newsletterForm');
  newsletterForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = newsletterForm.querySelector('input[name="email"]').value;
    const note = document.getElementById('newsletterNote');
    const data = await postJSON('ajax/newsletter_subscribe.php', { email });
    if (note) {
      note.textContent = data.message;
      note.style.color = data.success ? 'var(--success)' : 'var(--error)';
    }
    if (data.success) newsletterForm.reset();
  });

  /* -------------------------------------------------------------------
     PDP gallery + zoom
     ------------------------------------------------------------------- */
  const galleryMain = document.querySelector('.pdp-gallery-main');
  document.querySelectorAll('.pdp-thumb').forEach((thumb) => {
    thumb.addEventListener('click', () => {
      document.querySelectorAll('.pdp-thumb').forEach((t) => t.classList.remove('active'));
      thumb.classList.add('active');
      if (galleryMain) galleryMain.querySelector('img').src = thumb.querySelector('img').src;
    });
  });
  galleryMain?.addEventListener('click', () => galleryMain.classList.toggle('zoomed'));
  galleryMain?.addEventListener('mousemove', (e) => {
    if (!galleryMain.classList.contains('zoomed')) return;
    const rect = galleryMain.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    galleryMain.querySelector('img').style.transformOrigin = `${x}% ${y}%`;
  });

  /* -------------------------------------------------------------------
     Product detail tabs
     ------------------------------------------------------------------- */
  document.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      btn.closest('.pdp-tabs').querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
      btn.closest('.pdp-tabs').querySelectorAll('.tab-panel').forEach((p) => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(target)?.classList.add('active');
    });
  });

  /* -------------------------------------------------------------------
     FAQ accordion
     ------------------------------------------------------------------- */
  document.querySelectorAll('.faq-question').forEach((q) => {
    q.addEventListener('click', () => {
      q.closest('.faq-item').classList.toggle('open');
    });
  });

  /* -------------------------------------------------------------------
     Shop filters/sorting auto-submit
     ------------------------------------------------------------------- */
  const filtersForm = document.getElementById('filtersForm');
  filtersForm?.querySelectorAll('input[type="checkbox"], input[type="radio"], select').forEach((el) => {
    el.addEventListener('change', () => filtersForm.submit());
  });
  document.getElementById('mobileFilterToggle')?.addEventListener('click', () => {
    document.querySelector('.filters-panel')?.classList.toggle('open');
  });

  /* -------------------------------------------------------------------
     Checkout multi-step navigation
     ------------------------------------------------------------------- */
  const checkoutSteps = document.querySelectorAll('.checkout-step');
  const checkoutPanels = document.querySelectorAll('.checkout-panel');
  function goToCheckoutStep(stepNum) {
    checkoutSteps.forEach((s) => {
      const n = parseInt(s.dataset.step, 10);
      s.classList.toggle('active', n === stepNum);
      s.classList.toggle('done', n < stepNum);
    });
    checkoutPanels.forEach((p) => p.classList.toggle('active', parseInt(p.dataset.step, 10) === stepNum));
    window.scrollTo({ top: document.querySelector('.checkout-steps')?.offsetTop - 120 || 0, behavior: 'smooth' });
  }
  window.goToCheckoutStep = goToCheckoutStep;

  document.querySelectorAll('.checkout-next').forEach((btn) => {
    btn.addEventListener('click', () => {
      const panel = btn.closest('.checkout-panel');
      const inputs = panel.querySelectorAll('input[required], select[required]');
      for (const input of inputs) {
        if (!input.reportValidity()) return;
      }
      goToCheckoutStep(parseInt(panel.dataset.step, 10) + 1);
    });
  });
  document.querySelectorAll('.checkout-prev').forEach((btn) => {
    btn.addEventListener('click', () => {
      goToCheckoutStep(parseInt(btn.closest('.checkout-panel').dataset.step, 10) - 1);
    });
  });

  document.querySelectorAll('.payment-option').forEach((opt) => {
    opt.addEventListener('click', () => {
      opt.closest('fieldset').querySelectorAll('.payment-option').forEach((o) => o.classList.remove('selected'));
      opt.classList.add('selected');
      opt.querySelector('input[type="radio"]').checked = true;
    });
  });
  document.querySelectorAll('.delivery-option').forEach((opt) => {
    opt.addEventListener('click', () => {
      opt.closest('fieldset').querySelectorAll('.delivery-option').forEach((o) => o.classList.remove('selected'));
      opt.classList.add('selected');
      opt.querySelector('input[type="radio"]').checked = true;
      if (typeof window.onDeliveryMethodChange === 'function') window.onDeliveryMethodChange(opt.querySelector('input').value);
    });
  });

  /* -------------------------------------------------------------------
     Fade-in on scroll
     ------------------------------------------------------------------- */
  const observer = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 })
    : null;
  document.querySelectorAll('.fade-in').forEach((el) => (observer ? observer.observe(el) : el.classList.add('visible')));
})();
