
</main>

<section class="newsletter-band">
  <div class="container newsletter-inner">
    <h2>New Frames, In Your Inbox.</h2>
    <p>Get new arrivals, styling tips and exclusive offers.</p>
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
      <a href="<?= e(base_url('index.php')) ?>" class="logo"><img src="<?= e(base_url('assets/images/logo.png')) ?>" alt="MADIXX" class="logo-img"></a>
      <p>Sunglasses, spectacles and eyewear accessories, designed to see and be seen.</p>
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
        <li><a href="<?= e(base_url('sunglasses.php')) ?>">Sunglasses</a></li>
        <li><a href="<?= e(base_url('spectacles.php')) ?>">Spectacles</a></li>
        <li><a href="<?= e(base_url('accessories.php')) ?>">Accessories</a></li>
        <li><a href="<?= e(base_url('fitting-room.php')) ?>">Virtual Fitting Room</a></li>
        <li><a href="<?= e(base_url('collections.php')) ?>">Collections</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Discover</h3>
      <ul>
        <li><a href="<?= e(base_url('about.php')) ?>">About Us</a></li>
        <li><a href="<?= e(base_url('journal.php')) ?>">Style Journal</a></li>
        <li><a href="<?= e(base_url('quiz.php')) ?>">Frame Finder Quiz</a></li>
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
    <p>Crafted for clearer, sharper everyday style.</p>
  </div>
</footer>

<script src="<?= e(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>
