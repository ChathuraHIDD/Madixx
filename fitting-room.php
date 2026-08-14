<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

$gender = (string) ($_GET['gender'] ?? '');
$gender = in_array($gender, ['women', 'men'], true) ? $gender : '';

if ($gender === '') {
    $pageTitle = 'Virtual Fitting Room — MADIXX';
    $metaDescription = 'Try MADIXX frames on a virtual dummy before you buy — choose Gents or Women\'s to get started.';
    $canonicalPath = 'fitting-room.php';

    require __DIR__ . '/includes/header.php';
    ?>

<div class="container section-tight">
  <?= render_breadcrumbs([['label' => 'Virtual Fitting Room', 'url' => null]]) ?>
  <div class="section-heading text-center">
    <p class="eyebrow">Virtual Fitting Room</p>
    <h1 style="font-size:2.2rem;">Try Before You Buy</h1>
    <p>Choose a dummy to preview how our sunglasses and spectacles look before you order.</p>
  </div>

  <div class="fitting-landing">
    <a href="<?= e(base_url('fitting-room.php?gender=men')) ?>" class="fitting-gender-card">
      <img src="<?= e(base_url('assets/images/maledummy.png')) ?>" alt="Gents Fitting Room" loading="lazy">
      <div class="fitting-gender-card-overlay">
        <h3>Gents</h3>
        <span>Enter Fitting Room</span>
      </div>
    </a>
    <a href="<?= e(base_url('fitting-room.php?gender=women')) ?>" class="fitting-gender-card">
      <img src="<?= e(base_url('assets/images/womendummy.png')) ?>" alt="Women's Fitting Room" loading="lazy">
      <div class="fitting-gender-card-overlay">
        <h3>Women's</h3>
        <span>Enter Fitting Room</span>
      </div>
    </a>
  </div>
</div>

    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$genderLabel = $gender === 'women' ? "Women's" : 'Gents';
$dummyImage = $gender === 'women' ? 'assets/images/womendummy.png' : 'assets/images/maledummy.png';

$stmt = db()->prepare(
    "SELECT p.*, c.slug AS category_slug
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'active' AND c.slug IN ('sunglasses', 'spectacles')
     ORDER BY c.slug ASC, p.name ASC"
);
$stmt->execute();
$wearables = $stmt->fetchAll();
$sunglasses = array_values(array_filter($wearables, static fn ($p) => $p['category_slug'] === 'sunglasses'));
$spectacles = array_values(array_filter($wearables, static fn ($p) => $p['category_slug'] === 'spectacles'));

$pageTitle = $genderLabel . ' Virtual Fitting Room — MADIXX';
$metaDescription = 'Fit MADIXX sunglasses and spectacles to a ' . strtolower($genderLabel) . ' dummy and preview how each pair looks before you buy.';
$canonicalPath = 'fitting-room.php?gender=' . $gender;

require __DIR__ . '/includes/header.php';

$renderProductItem = static function (array $p) {
    $hasSale = $p['sale_price'] !== null && (float) $p['sale_price'] < (float) $p['price'];
    $price = $hasSale ? $p['sale_price'] : $p['price'];
    ?>
    <li class="fitting-product-item" data-name="<?= e(mb_strtolower($p['name'])) ?>">
      <img src="<?= e(base_url($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
      <div class="fitting-product-info">
        <span class="name"><?= e($p['name']) ?></span>
        <span class="price"><?= e(format_price($price)) ?></span>
      </div>
      <button
        type="button"
        class="fitting-tryon-btn"
        title="Fit to dummy"
        data-image="<?= e(base_url($p['image'])) ?>"
        data-name="<?= e($p['name']) ?>"
        data-price="<?= e(format_price($price)) ?>"
        data-slug="<?= e($p['slug']) ?>"
        data-id="<?= (int) $p['id'] ?>"
      ><i class="fa-solid fa-plus"></i></button>
    </li>
    <?php
};
?>

<div class="container section-tight">
  <?= render_breadcrumbs([
      ['label' => 'Virtual Fitting Room', 'url' => base_url('fitting-room.php')],
      ['label' => $genderLabel, 'url' => null],
  ]) ?>
  <div class="section-heading text-center" style="margin-bottom:24px;">
    <p class="eyebrow">Virtual Fitting Room</p>
    <h1 style="font-size:2.2rem;"><?= e($genderLabel) ?> Fitting Room</h1>
    <p>Pick a pair from either side, fit it to the dummy, and preview it before you buy.</p>
    <p style="margin-top:10px;">
      <a href="<?= e(base_url('fitting-room.php?gender=' . ($gender === 'women' ? 'men' : 'women'))) ?>" class="btn-text">
        Switch to the <?= $gender === 'women' ? 'Gents' : "Women's" ?> Fitting Room
      </a>
    </p>
  </div>

  <div class="fitting-search">
    <input type="search" id="fittingSearch" placeholder="Search sunglasses or spectacles to try on…" aria-label="Search products">
  </div>

  <div class="fitting-room-layout">
    <aside class="fitting-sidebar">
      <h4>Sunglasses</h4>
      <ul>
        <?php foreach ($sunglasses as $p): $renderProductItem($p); endforeach; ?>
      </ul>
      <p class="fitting-no-results">No sunglasses match your search.</p>
    </aside>

    <div class="fitting-stage" id="fittingStage">
      <div class="dummy-stage">
        <img src="<?= e(base_url($dummyImage)) ?>" class="dummy-img" alt="<?= e($genderLabel) ?> fitting room dummy">
        <div class="dummy-placeholder"><i class="fa-solid fa-glasses"></i></div>
        <div class="dummy-overlay"><img id="dummyOverlayImg" src="" alt=""></div>
      </div>
      <div class="fitting-caption" id="fittingCaption">
        <p class="empty">Select a pair from either side to fit it to the dummy.</p>
      </div>
    </div>

    <aside class="fitting-sidebar">
      <h4>Spectacles</h4>
      <ul>
        <?php foreach ($spectacles as $p): $renderProductItem($p); endforeach; ?>
      </ul>
      <p class="fitting-no-results">No spectacles match your search.</p>
    </aside>
  </div>
</div>

<script>
(function () {
  const stage = document.getElementById('fittingStage');
  const overlayImg = document.getElementById('dummyOverlayImg');
  const caption = document.getElementById('fittingCaption');
  const CFG = window.MADIXX || { baseUrl: '/' };

  document.querySelectorAll('.fitting-tryon-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.fitting-product-item.selected').forEach((li) => li.classList.remove('selected'));
      btn.closest('.fitting-product-item').classList.add('selected');

      overlayImg.src = btn.dataset.image;
      overlayImg.alt = btn.dataset.name;
      stage.classList.add('has-selection');

      caption.innerHTML =
        '<h3></h3><p class="price"></p><div class="fitting-caption-actions">' +
        '<a class="btn btn-outline btn-sm">View Details</a>' +
        '<button type="button" class="btn btn-primary btn-sm add-to-cart-btn">Add to Cart</button>' +
        '</div>';
      caption.querySelector('h3').textContent = btn.dataset.name;
      caption.querySelector('.price').textContent = btn.dataset.price;
      const viewLink = caption.querySelector('a');
      viewLink.href = CFG.baseUrl + 'product.php?slug=' + encodeURIComponent(btn.dataset.slug);
      caption.querySelector('.add-to-cart-btn').dataset.productId = btn.dataset.id;
    });
  });

  const searchInput = document.getElementById('fittingSearch');
  searchInput?.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    document.querySelectorAll('.fitting-sidebar').forEach((sidebar) => {
      let anyVisible = false;
      sidebar.querySelectorAll('.fitting-product-item').forEach((li) => {
        const match = li.dataset.name.includes(q);
        li.style.display = match ? '' : 'none';
        if (match) anyVisible = true;
      });
      const noResults = sidebar.querySelector('.fitting-no-results');
      if (noResults) noResults.style.display = anyVisible ? 'none' : 'block';
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
