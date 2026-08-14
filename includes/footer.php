
</main>

<section class="newsletter-band">
  <div class="container newsletter-inner">
    <h2>A Little Glow, In Your Inbox.</h2>
    <p>Get beauty tips, new launches and exclusive offers.</p>
    <form class="newsletter-form" id="newsletterForm">
      <?= csrf_field() ?>
      <input type="email" name="email" placeholder="Your email address" required aria-label="Email address">
      <button type="submit">Subscribe</button>
    </form>
    <p class="newsletter-note" id="newsletterNote"></p>
  </div>
</section>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a href="<?= e(base_url('index.php')) ?>" class="logo">MADIXX</a>
      <p>Skincare and cosmetics designed to enhance your natural glow.</p>
      <div class="social-links">
        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="#" aria-label="Pinterest"><i class="fa-brands fa-pinterest"></i></a>
        <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
      </div>
    </div>

    <div class="footer-col">
      <h3>Shop</h3>
      <ul>
        <li><a href="<?= e(base_url('skincare.php')) ?>">Skincare</a></li>
        <li><a href="<?= e(base_url('makeup.php')) ?>">Makeup</a></li>
        <li><a href="<?= e(base_url('body.php')) ?>">Body Care</a></li>
        <li><a href="<?= e(base_url('collections.php')) ?>">Collections</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Discover</h3>
      <ul>
        <li><a href="<?= e(base_url('about.php')) ?>">About Us</a></li>
        <li><a href="<?= e(base_url('journal.php')) ?>">Beauty Journal</a></li>
        <li><a href="<?= e(base_url('quiz.php')) ?>">Beauty Quiz</a></li>
        <li><a href="<?= e(base_url('faq.php')) ?>">FAQ</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Support</h3>
      <ul>
        <li><a href="<?= e(base_url('contact.php')) ?>">Contact Us</a></li>
        <li><a href="<?= e(base_url('order-tracking.php')) ?>">Track My Order</a></li>
        <li><a href="<?= e(base_url('account.php')) ?>">My Account</a></li>
        <li><a href="<?= e(base_url('faq.php')) ?>">Shipping &amp; Returns</a></li>
      </ul>
    </div>
  </div>

  <div class="container footer-bottom">
    <p>&copy; <?= date('Y') ?> MADIXX. All rights reserved.</p>
    <p>Crafted with care for your everyday ritual.</p>
  </div>
</footer>

<script src="<?= e(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
